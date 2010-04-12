<?php

/**
 * Contains the UrlHelper class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use MVCWebComponents\Register;

/**
 * The UrlHelper class provides URL converting functions to views.
 * 
 * @version 1.0
 */
class UrlHelper {
	
	public function native($path) {
		
		return str_replace('/', DS, $path);
		
	}
	
	public function short($path) {
		
		return MVCWebApp::shortPath($path);
		
	}
	
	public function long($path) {
		
		if(strpos($path, '[root]') === 0) $path = str_replace('[root]', '');
		$path = $this->native($path);
		if($path[0] === DS) $path = substr($path, 1);
		return Register::read('env.root_dir' . $path);
		
	}
	
}

?>