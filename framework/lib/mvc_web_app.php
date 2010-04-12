<?php

/**
 * Contains the MintyWebApp class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use \MVCWebComponents\Database\Database,
	\MVCWebComponents\MVCException,
	\MVCWebComponents\Register,
	\MVCWebComponents\Session,
	\MVCWebComponents\View,
	\MVCWebComponents\Router,
	\MVCWebComponents\Inflector,
	\MVCWebComponents\Autoloader;

/**
 * Framework helper class.
 * 
 * Contains useful functions used throughout the framework.
 * 
 * @version 0.2
 */
class MVCWebApp {
	
	/**
	 * An array of loaded configuration files.
	 * 
	 * @var array
	 * @since 0.1
	 */
	protected static $loadedConfigs = array();
	
	/**
	 * Return {@link $loadedConfigs}.
	 * 
	 * @return array
	 * @since 0.1
	 */
	public static function loadedConfigs() {
		
		return static::$loadedConfigs;
		
	}
	
	/**
	 * Setup the framework.
	 * 
	 * @return void
	 * @since 0.2
	 */
	public static function setup() {
		
		// Ensure we only run setup once.
		static $setup = false;
		if($setup) return;
		
		// Maximum error reporting when in debug.
		if(DEBUG) error_reporting(E_ALL | E_STRICT);
		else error_reporting(0);
		
		// Set the exception handler.
		set_exception_handler(array('\MVCWebApp\MVCWebApp', 'handleException'));
		
		// Start session handling.
		Session::start();
		
		// Load configurations.
		static::loadConfigurations();
		
		// Add pre/post paths to View for convenience.
		View::addPrePath(
			Register::read('env.app.views_dir'),
			Register::read('env.framework.views_dir')
		);
		View::addPostPath('.tpl');
		
		// Add some helper namespaces.
		View::addHelperNamespace('\\MVCWebApp\\');
		
		// Register hooks to Database to increase the sql benchmark.
		Database::addHook('beforeQuery', function() {
			Benchmark::start('query', null, true);
			if(!Benchmark::finished('sql')) {
				Benchmark::start('sql');
				Benchmark::end('sql');
			}
		});
		Database::addHook('afterQuery', function() {
			Benchmark::end('query');
			Benchmark::combine('sql', array('sql', 'query'));
		});
		
		$setup = true;
		
	}
	
	/**
	 * Get execution parameters from the url and register the controller.
	 * 
	 * @return void
	 * @since 0.2
	 */
	public static function route() {
		
		// Ensure we only perform routing once.
		static $routed = false;
		if($routed) return;
		
		// Get the URL from the path info.
		if(!isset($_SERVER['PATH_INFO']) or !$_SERVER['PATH_INFO']) $url = '/';
		else $url = $_SERVER['PATH_INFO'];
		
		// Get the params from the Router.
		$params = Router::route($url);
		if(!isset($params['controller']) or !isset($params['action']))
			throw new BadConnectionException();
		Register::write('params', $params);
		
		// Register the controller.
		$controller = Inflector::camelize($params['controller'] . '_controller');
		Autoloader::relax(); // Don't want missing class exceptions from Autoloader.
		if(!class_exists($controller))
			throw new MissingControllerException($controller);
		Register::write('controller', $controller::instance());
		
		$routed = true;
		
	}
	
	/**
	 * Executes the action.
	 * 
	 * @return string Any output from the action.
	 * @since 0.2
	 */
	public static function action() {
		
		ob_start();
		Register::read('controller')->action(Register::read('params.action'));
		return ob_get_clean();
		
	}
	
	/**
	 * Load all the configurations.
	 * 
	 * @param bool $force When true configurations are loaded regardless of whether they've been loaded before.
	 * @return void
	 * @since 0.1
	 */
	public static function loadConfigurations($force = false) {
		
		static $loaded = false;
		if($loaded and !$force) return;
		
		foreach(scandir(Register::read('env.app.configs_dir')) as $file) {
			if(substr($file, -4) != '.php') continue;
			
			$path = Register::read('env.app.configs_dir') . $file;
			include $path;
			
			static::$loadedConfigs[] = substr($file, 0, -4);
		}
		
		foreach(scandir(Register::read('env.framework.configs_dir')) as $file) {
			if(substr($file, -4) != '.php') continue;
			if(in_array(substr($file, 0, -4), static::$loadedConfigs)) return;
			
			$path = Register::read('env.framework.configs_dir') . $file;
			include $path;
			
			static::$loadedConfigs[] = substr($file, 0, -4);
		}
		
		Register::write('configs', static::$loadedConfigs);
		$loaded = true;
		
	}
	
	/**
	 * Loads a specific configuration file.
	 * 
	 * @param string $config The name of the config file sans '.php' extension.
	 * @param bool   $force  When true the config is loaded regardless of whether it's already in $loadedConfigs.
	 * @return bool True on success, false if config already loaded.
	 * @throws MissingConfigException Thrown when the named config is missing.
	 * @since 0.2
	 */
	public static function loadConfiguration($config, $force = false) {
		
		if(in_array($config, static::$loadedConfigs) and !$force) return false;
		
		$return = false;
		
		$file = Register::read('env.app.configs_dir') . "$config.php";
		if(file_exists($file)) {
			include $file;
			$return = true;
		}
		
		$file = Register::read('env.framework.configs_dir') . "$config.php";
		if(file_exists($file)) {
			include $file;
			$return = true;
		}
		
		if($return) {
			Register::append('configs', $config);
			return true;
		}
		throw new MissingConfigException($config);
		
	}
	
	/**
	 * The application's exception handler.
	 * 
	 * Prints a formatted message if the exception is an MVCWebComponents\MVCException 
	 * derivative, the regular message otherwise.
	 * 
	 * @param Exception $e
	 * @return void
	 * @since 0.1
	 */
	public static function handleException($e) {
		
		if(DEBUG) {
			if($e instanceof \MVCWebComponents\MVCException) $msg = $e->getFormattedMsg();
			else $msg = $e->getMessage();
		}else $msg = 'Internal server error.';
		
		die(static::shortPath($msg));
		
	}
	
	/**
	 * Removes path details before the root directory.
	 * 
	 * E.g. "C:\Something\SomethingElse\mvc-web-app\app\views" would become "[root]\app\views"
	 * 
	 * @param $path The original string.
	 * @return string The string with all paths shortened.
	 * @since 0.2
	 */
	public static function shortPath($path) {
		
		return str_replace(Register::read('env.root_dir'), '[root]' . DS, $path);
		
	}
	
}

/**
 * Exception thrown when a missing controller is encountered.
 * 
 * @version 1.0
 */
class MissingControllerException extends MVCException {
	
	/**
	 * Sets the message.
	 * 
	 * @param string $controller
	 * @return void
	 * @since 1.0
	 */
	public function __construct($controller) {
		
		$this->message = "Missing controller $controller.";
		
	}
	
}

/**
 * Exception thrown when a missing action is encountered.
 * 
 * @version 1.0
 */
class MissingActionException extends MVCException {
	
	/**
	 * Sets the message.
	 * 
	 * @param string $action
	 * @return void
	 * @since 1.0
	 */
	public function __construct($action) {
		
		$this->message = "Missing action $action.";
		
	}
	
}

/**
 * Exception thrown when a missing view is encountered.
 * 
 * @version 1.0
 */
class MissingViewException extends MVCException {
	
	/**
	 * Sets the message.
	 * 
	 * @param string $view
	 * @return void
	 * @since 1.0
	 */
	public function __construct($view) {
		
		$this->message = "Missing view $view.";
		
	}
	
}

/**
 * Exception thrown when a matched connection is missing a controller 
 * or action parameter.
 * 
 * @version 1.0
 */
class BadConnectionException extends MVCException {
	
	/**
	 * Sets the message.
	 * 
	 * @return void
	 * @since 1.0
	 */
	public function __construct() {
		
		$this->message  = "Route descriptions must have a 'controller' and 'action' paremeter, found:<br/>";
		$this->message .= Debug::var_dump(array(
			'pattern' => Router::$connection['urlPattern'],
			'parameters' => Router::$connection['parameters'],
			false, false	
		));
		
	}
	
}

?>