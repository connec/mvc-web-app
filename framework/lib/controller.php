<?php

/**
 * Contains the Controller class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use MVCWebComponents\Hookable,
	MVCWebComponents\Benchmark,
	MVCWebComponents\Register,
	MVCWebComponents\View,
	MVCWebComponents\Autoloader;

/**
 * The Controller class handles execution of the action and communicates with 
 * the view.  Derivatives should contain most application logic.
 * 
 * @version 1.3
 */
abstract class Controller extends Hookable {
	
	/**
	 * Sets whether or not this is a private controller.
	 * 
	 * Private controllers cannot be called from urls.
	 * 
	 * @var bool
	 * @since 1.0
	 */
	protected $private = false;
	
	/**
	 * Contains an array of variable => value pairs to pass to the view/layout.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected $register = array();
	
	/**
	 * Contains an array of helpers to be used with the views.
	 * 
	 * @var array
	 * @since 1.1
	 */
	protected $helpers = array();
	
	/**
	 * Sets whether or not to render the layout after the action is performed.
	 * 
	 * @var bool
	 * @since 1.0
	 */
	protected $render = true;
	
	/**
	 * The layout to use for rendering.
	 * 
	 * A layout is just a template which is passed the result of the view in $action_output.
	 * 
	 * If false value (i.e. 0, false, null, etc) then no layout is used.
	 * 
	 * @var mixed
	 * @since 1.0
	 */
	protected $layout = 'default';
	
	/**
	 * The view to use for rendering.
	 * 
	 * The view is rendered before, and inserted into, the layout.
	 * 
	 * Can be a string or left empty to select the template automatically.
	 * 
	 * @var mixed
	 * @since 
	 */
	protected $view;
	
	/**
	 * Array of available hooks.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $hooks = array(
		'beforeAction',
		'afterAction',
		'beforeRender',
		'afterRender');
	
	/**
	 * Array of functions to execute before an action is executed.
	 * 
	 * To call the function on the controller instance '$this', set 
	 * '$this' as the class name in the callback.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $beforeAction = array();
	
	/**
	 * Array of functions to execute after an action is executed.
	 * 
	 * To call the function on the controller instance '$this', set 
	 * '$this' as the class name in the callback.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $afterAction = array();
	
	/**
	 * Array of functions to execute before rendering any output.
	 * 
	 * To call the function on the controller instance '$this', set 
	 * '$this' as the class name in the callback.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $beforeRender = array();
	
	/**
	 * Array of functions to execute after rendering any output.
	 * 
	 * To call the function on the controller instance '$this', set 
	 * '$this' as the class name in the callback.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected static $afterRender = array();
	
	/**
	 * An array of processed $_POST and $_FILE data.
	 * 
	 * @var array
	 * @since 1.2
	 */
	protected $data = array();
	
	/**
	 * Controllers should be singleton, protect the constructor.
	 * 
	 * @return void
	 * @since 1.0
	 */
	protected function __construct() {}
	
	/**
	 * Controllers should be singleton, protect the clone magic function.
	 * 
	 * @return void
	 * @since 1.0
	 */
	final protected function __clone() {}
	
	/**
	 * Treats attempts to assign missing variables as a shortcut for set().
	 * 
	 * @param string $var The name of the variable to set.
	 * @param mixed  $val The value to assign.
	 * @return void
	 * @since 1.3
	 */
	public function __set($var, $val) {
		
		$this->set($var, $val);
		
	}
	
	/**
	 * Returns the singleton instance of this controller.
	 * 
	 * @return Controller A controller instance.
	 * @since 1.0
	 */
	final public static function &instance() {
		
		static $instances = array();
		$class = get_called_class();
		if(!isset($instance[$class])) $instances[$class] = new $class;
		
		return $instances[$class];
		
	}
	
	/**
	 * Stores a variable => value pair in the register.
	 * 
	 * @param string $var
	 * @param mixed  $val
	 * @return void
	 * @since 1.0
	 */
	public function set($var, $val) {
		
		$this->register[$var] = $val;
		
	}
	
	/**
	 * Retrives a variable from the register.
	 * 
	 * @param string $var
	 * @return mixed
	 * @since 1.0
	 */
	public function get($var) {
		
		return $this->register[$var];
		
	}
	
	/**
	 * Performs the given action.
	 * 
	 * Performs the given action and handles the selection of and assigning to
	 * the view/layout if required.
	 * 
	 * @param string $action The name of the action to perform.
	 * @param bool   $force  Unless this is true, trying to perform an action on a private controller will raise an exception.
	 * @since 1.0
	 * @throws MissingActionException Thrown when the given action isn't defined.
	 */
	public function action($name, $force = false) {
		
		if($this->private and !$force) {
			if(DEBUG) throw new MVCException('Tried to perform private controller action without $force.');
			throw new MissingControllerException(get_class($this));
		}else {
			$methods = get_class_methods($this);
			if(!in_array($name, $methods) and !in_array('__call', $methods))
				throw new MissingActionException(get_class($this), $name);
		}
		
		static::runHook('beforeAction', $this);
		
		// Sort out any request data.
		$this->sortData();
		
		// Add controller pre paths to the View.
		View::addPrePath(
			Register::read('env.app.views_dir') . Register::read('params.controller') . DS,
			Register::read('env.framework.views_dir') . Register::read('params.controller') . DS
		);
		
		// Execute the action function, passing 'other' parameters as arguments.
		call_user_func_array(array($this, $name), Register::read('params.other'));
		
		// If we're not rendering then there's nothing else to do.
		if(!$this->render) return;
		
		// Sort out the view.
		if(is_string($this->view)) $view = new View($this->view);
		else $view = new View($name);
		$view->set($this->register);
		
		// Sort out the layout.
		$layout = null;
		if($this->layout) {
			$layout = new View("layouts/$this->layout");
			$layout->set($this->register);
			
			// Give the view a functions for delegating a variable to the layout.
			$view->set('delegate', function($var, $val) use ($layout) {
				$layout->set($var, $val);
			});
		}
		
		// Allow the layout/view to be modified by any hooks.
		static::runHook('beforeRender', $this, array(&$layout, &$view));
		
		// Render everything.
		if($layout) {
			// Render the view to the layout.
			$layout->set('action_output', $view->render(true));
			
			// Render the layout.
			$output = $layout->render(true);
		}else
			$output = $view->render(true); // Just render the view.
		
		// Allow the output to be modified by any hooks.
		static::runHook('afterRender', $this, array(&$output));
		
		// Finally, display the output.
		echo $output;
		
		static::runHook('afterAction', $this);
		
	}
	
	/**
	 * Sorts all request data into {@link $data} for convenient use.
	 * 
	 * Also applies some additional processing.
	 * 
	 * @return void
	 * @since 1.2
	 */
	protected function sortData() {
		
		// Only sortData once.
		static $sorted = false;
		if($sorted) return;
		
		// Sort any POST data.
		foreach($_POST as $k => $v) {
			// If it's model data create a new model for it.
			Autoloader::relax();
			if(class_exists($k) and is_subclass_of($k, '\\MVCWebComponents\\Model\\Model'))
				$this->data[$k] = new $k($_POST[$k]);
			else // Otherwise just store it
				$this->data[$k] = $v;
		}
		
		// Sort any uploaded file data.
		foreach($_FILES as $k => $v) {
			if(!is_array($v)) throw new MVCException('Bad $_FILES format.');
			if(isset($v['name']) and is_array($v['name'])) { // It's an array field
				$this->data[$k] = array();
				foreach($v['name'] as $_k => $name) {
					$file = array();
					foreach(array_keys($v) as $key)
						$file[$key] = $v[$key][$_k];
					$this->data[$k][] = new UploadedFile($file);
				}
			}else
				$this->data[$k] = new UploadedFile($v);
		}
		
		// Update the sorted flag.
		$sorted = true;
		
	}
	
	/**
	 * Redirects to a given url after given number of seconds.
	 * 
	 * @param string $url
	 * @param int    $wait
	 * @param string $message An optional flash message to set.
	 * @param string $type    An optional type to pass to {@link Controller::flash()}.
	 * @return void
	 * @since 1.1
	 */
	protected function redirect($url, $wait = 0, $message = '', $class = '') {
		
		if($message) $this->flash($message, $class);
		
		// Sort the url.
		$url = UrlHelper::fix($url);
		
		// If we're in debug print a link to the url and die.
		if(DEBUG) {
			$text = "Redirect:<br/><a href=\"$url\">$url</a>: $message";
			if(!$wait) die($text);
			else echo $text;
		}
		
		if($wait) header("Refresh: $wait; $url");
		else {
			header("Location: $url");
			exit;
		}
		
	}
	
	/**
	 * Sets a message to the session for display after redirection.
	 * 
	 * @param string $message
	 * @param string $type Optional parameter set in the session for later logic.
	 * @return void
	 * @since 1.1
	 */
	protected function flash($message, $class = '') {
		
		Session::write('flash.message', $message);
		Session::write('flash.class', $class);
		
	}
	
}

?>