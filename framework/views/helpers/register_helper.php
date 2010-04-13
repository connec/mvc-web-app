<?php

/**
 * Contains the RegisterHelper class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;

/**
 * The RegisterHelper class provides Register access to views.
 * 
 * @version 1.0
 */
class RegisterHelper {
	
	/**
	 * Delegate any calls to MVCWebComponents\Register.
	 * 
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @return mixed
	 * @since 1.0
	 */
	public function __call($name, $args) {
		
		return call_user_func_array(array('\MVCWebComponents\Register', $name), $args);
		
	}
	
}

?>