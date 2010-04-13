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
	
	public static function native($path) {
		
		return str_replace('/', DS, $path);
		
	}
	
	public static function short($path) {
		
		return MVCWebApp::shortPath($path);
		
	}
	
	public static function long($path) {
		
		if(strpos($path, '[root]') === 0) $path = str_replace('[root]', '');
		$path = static::native($path);
		if($path[0] === DS) $path = substr($path, 1);
		return Register::read('env.root_dir' . $path);
		
	}
	
	public static function fix($url) {
		
		// If it's a full url leave it alone.
		if(strpos($url, 'http://') !== 0) {
			// If it's a 'root' url strip the first '/'
			if(strpos($url, '\\\\') === 0) $url = substr($url, 1);
			else { // It's an application url.
				if($url[0] == '/') $url = substr($url, 1);
				$url = Register::read('env.root_url') . $url;
			}
		}
		return $url;
		
	}
	
}

?>