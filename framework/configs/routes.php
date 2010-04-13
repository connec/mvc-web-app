<?php

/**
 * Route configuration.
 */

$routes = array(
	array('pattern' => array('/:controller/:action', '/:controller/:action/*')),
	array(
		'pattern' => '/',
		'params' => array('controller' => 'pages', 'action' => 'display', 'home')),
	array(
		'pattern' => '/*',
		'params' => array('controller' => 'pages', 'action' => 'display'))
);

?>