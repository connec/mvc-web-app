<?php

/**
 * Application dispatcher.
 * 
 * Gets application parameters from the Router for the given URL and calls
 * upon the appropriate controller and action.
 * 
 * @version 0.1
 */
namespace MVCWebApp;

/* Import the required classes from their namespaces. */
use \MVCWebComponents\Autoloader,
	\MVCWebComponents\Session,
	\MVCWebComponents\Register,
	\MVCWebComponents\Router,
	\MVCWebComponents\MVCException,
	\MVCWebComponents\Inflector,
	\MVCWebComponents\Debug,
	\MVCWebComponents\View,
	\MVCWebComponents\Database\Database,
	\MVCWebComponents\Benchmark;

/* Start the setup benchmark */
$start = microtime(true);

/* Setup framework constants. */

/**
  * The version of the mvc-web-app framework.
  * 
  * @since 0.1
  */
define('MVC_WEB_APP', 0.1);


/**
 * The debug level (0 = off, 1 = on).
 * 
 * When DEBUG is 1 the framework will provide debug information to the layout and
 * redirects will display a link instead of forcing a redirection.
 * 
 * @since 0.1
 */
define('DEBUG', 1);

/**
 * Shorthand to the system directory separator.
 * 
 * @since 0.1
 */
define('DS', DIRECTORY_SEPARATOR);

/* Get the environment details */
$start = microtime(true);

$env['root_dir']    = getcwd() . DS;
$env['img_dir']     = $env['root_dir'] . 'img' . DS;
$env['scripts_dir'] = $env['root_dir'] . 'scripts' . DS;
$env['styles_dir']  = $env['root_dir'] . 'styles' . DS;

$env['app'] = array();
$env['framework'] = array();

$env['app']['dir']       = $env['root_dir'] . 'app' . DS;
$env['framework']['dir'] = $env['root_dir'] . 'framework' . DS;

foreach(array('configs', 'controllers', 'lib', 'models', 'views') as $dir) {
	$env['app'][$dir . '_dir']       = $env['app']['dir'] . $dir . DS;
	$env['framework'][$dir . '_dir'] = $env['framework']['dir'] . $dir . DS;
}

$env['app']['helpers_dir']       = $env['app']['views_dir'] . 'helpers' . DS;
$env['framework']['helpers_dir'] = $env['framework']['views_dir'] . 'helpers' . DS;

$env['root_url']    = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
$env['full_url']    = 'http://' . $_SERVER['SERVER_NAME'] . $env['root_url'];
$env['img_url']     = $env['root_url'] . 'img/';
$env['scripts_url'] = $env['root_url'] . 'scripts/';
$env['styles_url']  = $env['root_url'] . 'styles/';

// TEST: link directly to mvc-web-components
$env['framework']['lib_dir'] = $env['root_dir'] . '..' . DS . 'mvc-web-components' . DS;

/* Setup class autoloading */
require_once $env['framework']['lib_dir'] . 'mvc_exception.php';
require_once $env['framework']['lib_dir'] . 'inflector.php';
require_once $env['framework']['lib_dir'] . 'autoloader.php';
Autoloader::addDirectory(
	// In decreasing order of priority
	$env['app']['controllers_dir'],
	$env['app']['lib_dir'],
	$env['app']['models_dir'],
	$env['app']['helpers_dir'],
	$env['framework']['controllers_dir'],
	$env['framework']['lib_dir'],
	$env['framework']['models_dir'],
	$env['framework']['helpers_dir'],
	false // don't check the directories
);

// TEST SETUP: add mvc-web-components to autoload path.
Autoloader::addDirectory(
	$env['framework']['dir'] . 'lib' . DS,
	$env['root_dir'] . '..' . DS . 'mvc-web-components' . DS . 'Model',
	$env['root_dir'] . '..' . DS . 'mvc-web-components' . DS . 'Database'
);

/* Write the environment to the Register */
Register::write('env', $env);

/* Move benchmarking to the Benchmark class */
Benchmark::write('sql_total', 0);

Benchmark::write('environment_start', $start);
Benchmark::end('environment');

Benchmark::write('total_start', $start);
Benchmark::write('setup_start', $start);

/* Setup error handling. */

Benchmark::start('configuration');

// Maximum error reporting.
error_reporting(E_ALL | E_STRICT);

// Set the exception handler.
set_exception_handler(array('\MVCWebApp\MVCWebApp', 'handleException'));

/* Start session handling. */
Session::start();

/* Load configurations */
MVCWebApp::loadConfigurations();

/* Framework specific View config */
View::addPrePath(
	Register::read('env.app.views_dir'),
	Register::read('env.framework.views_dir')
);
View::addPostPath('.tpl');

Benchmark::end('configuration');

/* Do the actual dispatching */

Benchmark::start('routing');

// Get the URL from the path info.
if(!isset($_SERVER['PATH_INFO']) or !$_SERVER['PATH_INFO']) $url = '/';
else $url = $_SERVER['PATH_INFO'];

// Get the params from the Router.
$params = Router::route($url);
if(!isset($params['controller']) or !isset($params['action'])) {
	ob_start();
	var_dump(array('pattern' => Router::$connection['urlPattern'], 'params' => Router::$connection['parameters']));
	$dump = ob_get_clean();
	throw new MVCException('No controller/action params provided for route:' . $dump);
}
Register::write('params', $params);

$controller = Inflector::camelize($params['controller'] . '_controller');
Autoloader::relax(); // Don't want missing class exceptions from Autoloader.
if(!class_exists($controller))
	throw new MissingControllerException($controller);
Register::write('controller', $controller::instance());

Benchmark::end('routing');
Benchmark::end('setup');

/* Do the action */
Benchmark::start('action');

ob_start();
Register::read('controller')->action($params['action']);
$output = ob_get_clean();

Benchmark::end('action');

Benchmark::end('total');

/* Cleanup */
// If we're debugging add some debugging info.
if(DEBUG) {
	$view = new View('debug/debug');
	$view->query_table = View::partial(
		'debug/_query_table',
		array('queries' => Database::getQueries()),
		true
	);
	$view->watched_table = View::partial(
		'debug/_watch_table',
		array('watched' => Debug::summary()),
		true
	);
	$view->benchmarks_table = View::partial(
		'debug/_benchmark_table',
		array('benchmarks' => Benchmark::summary()),
		true
	);
	$output = str_replace(':debug:', $view->render(true), $output);
}

echo $output;

?>