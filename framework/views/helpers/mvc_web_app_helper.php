<?php

/**
 * Contains the MvcWebAppHelper class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;

/**
 * The MVCWebApp class provides functions for checking the state/configuration 
 * of the application to the view.
 * 
 * @version 1.0
 */
class MvcWebAppHelper {
	
	/**
	 * Checks if a named config is loaded.
	 * 
	 * @param string $name
	 * @return bool
	 * @since 1.0
	 */
	public function checkConfig($name) {
		
		return in_array($name, MVCWebApp::loadedConfigs());
		
	}
	
}

?>