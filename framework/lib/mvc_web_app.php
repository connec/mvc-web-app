<?php

/**
 * Contains the MintyWebApp class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use \MVCWebComponents\MVCException,
	\MVCWebComponents\Register;

/**
 * Framework helper class.
 * 
 * Contains useful functions used throughout the framework.
 * 
 * @version 0.1
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
			if($e instanceof \MVCWebComponents\MVCException) die($e->getFormattedMsg());
			else die($e->getMessage());
		}else die('Internal server error.');
		
	}
	
	/**
	 * Load all the configurations.
	 * 
	 * @return void
	 * @since 0.1
	 */
	public static function loadConfigurations() {
		
		foreach(scandir(Register::read('env.app.configs_dir')) as $file) {
			if(substr($file, -4) != '.php') continue;
			
			$path = Register::read('env.app.configs_dir') . $file;
			require_once $path;
			
			static::$loadedConfigs[] = $file;
		}
		
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

?>