<?php

/**
 * Contains the Controller class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use \MVCWebComponents\Hookable,
	\MVCWebComponents\Benchmark,
	\MVCWebComponents\Register,
	\MVCWebComponents\View;

/**
 * The Controller class handles execution of the action and communicates with 
 * the view.  Derivatives should contain most application logic.
 * 
 * @version 1.0
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
	 * Array of available hooks.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected $hooks = array(
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
	protected $beforeAction = array();
	
	/**
	 * Array of functions to execute after an action is executed.
	 * 
	 * To call the function on the controller instance '$this', set 
	 * '$this' as the class name in the callback.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected $afterAction = array();
	
	/**
	 * Array of functions to execute before rendering any output.
	 * 
	 * To call the function on the controller instance '$this', set 
	 * '$this' as the class name in the callback.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected $beforeRender = array();
	
	/**
	 * Array of functions to execute after rendering any output.
	 * 
	 * To call the function on the controller instance '$this', set 
	 * '$this' as the class name in the callback.
	 * 
	 * @var array
	 * @since 1.0
	 */
	protected $afterRender = array();
	
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
		}elseif(!in_array($name, get_class_methods($this)))
			throw new MissingActionException(get_class($this), $name);
		
		static::runHook('beforeAction', $this);
		
		// Add controller pre paths to the View.
		View::addPrePath(
			Register::read('env.app.views_dir') . Register::read('params.controller') . DS,
			Register::read('env.framework.views_dir') . Register::read('params.controller') . DS
		);
		
		// Execute the action function, passing 'other' parameters as arguments.
		call_user_func_array(array($this, $name), Register::read('params.other'));
		
		// If we're not rendering then there's nothing else to do.
		if(!$this->render) {
			Benchmark::end('action');
			return;
		}
		
		// Sort out the view.
		if(is_string($this->view)) $view = new View($this->view);
		else $view = new View($name);
		$view->set($this->register);
		
		// Sort out the layout.
		if($this->layout) {
			$layout = new View("layouts/$this->layout");
			$layout->set($this->register);
			$layout->action_output = $view->render(true);
		}
		
		// Render everything.
		Benchmark::start('render');
		static::runHook('beforeRender', $this, array(&$layout, &$view));
		
		if($layout) $output = $layout->render(true);
		else $output = $view->render(true);
		
		static::runHook('afterRender', $this, array(&$output));
		echo $output;
		
		Benchmark::end('render');
		
		static::runHook('afterAction', $this);
		
	}
	
	
}

?>