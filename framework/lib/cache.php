<?php

/**
 * Contains the Cache class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use MVCWebComponents\Database\Database,
	MVCWebComponents\Register,
	MVCWebComponents\MVCException,
	MVCWebComponents\BadArgumentException;

/**
 * Cache class provides an interface for caching and recovering arbitrary 
 * information.
 * 
 * @version 1.0
 */
class Cache {
	
	/**
	 * An array of values cached to the 'reg'.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $register = array();
	
	/**
	 * Writes a value to a cache.
	 * 
	 * Possible values for $type are:
	 *    'reg': Caches the serialized value in the $register array.
	 *    'db': Caches the serialized value in the `cache` table.
	 *    'file': Caches the serizlied value in a file.
	 * 
	 * @param string $name  The name to store the value under.
	 * @param mixed  $value The value to cache.  Stored serialize()'d.
	 * @param string $type  The type of caching to use.  Either 'reg', 'db' or 'file'.
	 * @return bool  True on caching success, false on failure.
	 * @since 1.0
	 */
	public static function write($name, $value, $type = 'file') {
		
		$value = serialize($value)
		switch($type) {
			case 'reg':
				static::$register[$name] = $value;
				return true;
				break;
			case 'db':
				$name = Database::escape($name);
				$value = Database::escape($value);
				$sql = "insert into `cache` (`name`, `value`) values ('$name', '$value');";
				return Database::query($sql);
				break;
			case 'file':
				$name = preg_replace('[^[a-z0-9,\._]', '_', $name);
				$file = Register::read('env.cache_dir') . $name;
				if($fh = fopen($file, 'w')) {
					if(fwrite($fh, $value) !== false) {
						fclose($fh);
						return true;
					}else
						throw new MVCException("Failed to write '$value' to cache file '$file'.");
				}else
					throw new MVException("Failed to open '$file' for writing.");
				break;
			default:
				throw new BadArgumentException("Cache::write() expects parameter 3 to be one of 'reg', 'db' or 'file', '$type' given.");
				break;
		}
		
	}
	
	/**
	 * Retrives a value from a cache.
	 * 
	 * @param string $name The name of the cached value to read.
	 * @param string $type Optional.  If given only the $type cache is checked (reg/db/file).  If not given all are checked (slower).
	 * @return mixed The unserialized value.
	 * @since 1.0
	 */
	public static function read($name, $type = 'all') {
		
		$type = static::check($name, $type);
		if(!$type) throw new MissingCacheException($name, $type);
		
		switch($type) {
			case 'reg':
				return unserialize(static::$register[$name]);
				break;
			case 'db':
				$name = Database::escape($name);
				$row = Database::getRow('array'); // query should still be set from Cache::check()
				return unserialize($row['value']);
			case 'file':
				$name = preg_replace('[^[a-z0-9,\._]', '_', $name);
				$file = Register::read('env.cache_dir') . $name;
				return unserialize(file_get_contents($file));
				break;
			default: // Should never happen
				throw new BadArgumentException("Cache::read() expects parameter 2 to be 'all', 'reg', 'db' or 'file'. '$type' given.");
				break;
		}
		
	}
	
	/**
	 * Checks if a cache value is set.
	 * 
	 * @param string $name The name of the cache value to check for.
	 * @param string $type Optional.  If given only the $type cache is check (reg/db/file).  If ommitted, all are checked.
	 * @return mixed The cache type where the value was found or false if it was not found.
	 * @since 1.0
	 */
	public static function check($name, $type = 'all') {
		
		switch($type) {
			case 'all':
				return static::check($name, 'reg') ?: (static::check($name, 'db') ?: static::check($name, 'file'));
				break;
			case 'reg':
				return isset(static::$register[$name]) ? 'reg' : false;
				break;
			case 'db':
				$name = Database::escape($name);
				Database::query("select * from `cache` where `name` = '$name';");
				return Database::getNumResultRows() > 0 ? 'db' : false;
				break;
			case 'file':
				$name = preg_replace('[^[a-z0-9,\._]', '_', $name);
				$file = Register::read('env.cache_dir') . $name;
				return is_readable($file) ? 'file' : false;
				break;
			default:
				throw new BadArgumentException("Cache::check() expects parameter 2 to be 'all', 'reg', 'db' or 'file'.  '$type' given.");
				break;
		}
	}
	
}

/**
 * An exception thrown when {@link Cache::read()} encounters a missing cache.
 * 
 * @version 1.0
 */
class MissingCacheException extends MVCException {
	
	/**
	 * Sets the message.
	 *
	 * @param string $name
	 * @param string $type
	 * @return void
	 * @since 1.0
	 */
	public function __construct($name, $type) {
		
		$this->message = "Can not read from missing cache '$name' in $type caches.";
		
	}
	
} 

?>