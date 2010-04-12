<?php

/**
 * Contains the SessionHelper class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;

/**
 * The SessionHelper class provides easy session access from inside a view.
 * 
 * @version 1.0
 */
class SessionHelper {
	
	/**
	 * Passes all calls to the mvc-web-components' Session class.
	 * 
	 * @param string $name
	 * @param array  $args
	 * @return mixed
	 * @since 1.0
	 */
	public function __call($name, $args) {
		
		return call_user_func_array(array('\MVCWebComponents\Session', $name), $args);
		
	}
	
}

?>