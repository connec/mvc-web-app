<?php

/**
 * Contains the UploadedFile class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use MVCWebComponents\BadArgumentException,
	MVCWebComponents\Inflector;

/**
 * The UploadedFile class provides a pleasant interface for handling uploaded files.
 * 
 * IMPORTANT: Within the framework this class is initialized from UNFILTERED $_FILES 
 * data, there are/will be functions within the class for checking/validating a file 
 * but it is important to review these (especially file names) as they are untrusted.
 * 
 * @version 0.2
 */
class UploadedFile {
	
	/**
	 * The name of the file as reported by the browser.
	 * 
	 * Do not trust the default value as it is assigned unfiltered from $_FILES 
	 * data which is itself comes from the browser.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $name;
	
	/**
	 * The MIME type of the file as reported from (I think) the browser.
	 * 
	 * The default value should be treated with caution as it is likely set to 
	 * by the browser.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $type;
	
	/**
	 * The temporary location of the uploaded file.
	 * 
	 * The default value for this should NOT be changed as this is set by PHP 
	 * and cannot be affected by the client.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $tmp_name;
	
	/**
	 * The location of the file after being moved.
	 * 
	 * @var string
	 * @since 0.2
	 */
	protected $path;
	
	/**
	 * The error constant of the file upload.
	 * 
	 * @var int
	 * @since 0.1
	 */
	protected $error;
	
	/**
	 * The size of the uploaded file.
	 * 
	 * This is also set by PHP and so can be trusted.
	 * 
	 * @var int
	 * @since 0.1
	 */
	protected $size;
	
	/**
	 * Dynamic getter functions.
	 * 
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 * @since 0.2
	 */
	public function __call($name, $args) {
		
		if(substr($name, 0, 3) !== 'get')
			trigger_error('No such method: ' . get_class($this) . "::$name()", E_USER_ERROR);
		
		list(,$var) = explode('_', Inflector::underscore($name));
		if(in_array($var, array('name', 'type', 'error', 'size', 'path')))
			return $this->$var;
		
	}
	
	/**
	 * Initialize the file with it's $_FILES data.
	 * 
	 * @param array $details
	 * @return void
	 * @since 0.1
	 */
	public function __construct($details) {
		
		// Check the array.
		foreach(array('name', 'type', 'tmp_name', 'error', 'size') as $field) {
			if(!isset($details[$field]))
				throw new BadArgumentException(
					'UploadedFile::__construct() expects parameter one to be an array 
					containing all the keys "name", "type", "tmpName", "error" and "size".  
					Given:' . print_r($details, true));
			else $this->{$field} = $details[$field];
		}
		
	}
	
	/**
	 * Moves an uploaded file to the specified destination.
	 * 
	 * @param string $path
	 * @return bool True on success, false on failure.
	 * @since 0.2
	 */
	public function move($path) {
		
		if(move_uploaded_file($this->tmp_name, $path)) {
			$this->path = $path;
			return true;
		}
		return false;
		
	}
	
	/**
	 * Deletes the file if it's been moved.
	 * 
	 * @return void
	 * @since 0.2
	 */
	public function unlink() {
		
		if(!empty($this->path)) unlink($this->path);
		
	}
	
}

?>