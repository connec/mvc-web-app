<?php

/**
 * Route configuration.
 */

use MVCWebComponents\Router;

// EDIT BELOW ///////////////////////////////////////////////////////////////

$routes = array(
	array('pattern' => array('/:controller/:action', '/:controller/:action/*')),
	array(
		'pattern' => '/',
		'params' => array('controller' => 'pages', 'action' => 'display', 'home')),
	array(
		'pattern' => '/*',
		'params' => array('controller' => 'pages', 'action' => 'display'))
);

// END EDIT /////////////////////////////////////////////////////////////////
foreach($routes as $route) {
	Router::connect($route['pattern'], isset($route['params']) ? $route['params'] : null);
}

?>