<?php

/**
 * Contains the HtmlHelper class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;

/**
 * The HtmlHelper class provides useful, commonly used HTML generation functions.
 * 
 * @version 1.0
 */
class HtmlHelper {
	
	/**
	 * Redirect all calls to static calls.
	 * 
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @return mixed
	 * @since 1.0
	 */
	public function __call($name, $args) {
		return call_user_func_array(array('\MVCWebApp\HtmlHelper', $name), $args);
	}
	
	/**
	 * Returns an arbitrary html tag with given content and attributes.
	 * 
	 * @param string $tag        The tag to print.
	 * @param string $content    The content of the tag.  If empty will print a short tag.
	 * @param array  $attributes An array of attributes for the tag.
	 * @return void
	 * @since 1.0
	 */
	public static function tag($tag, $content = '', $attributes = array()) {
		
		$out = "<$tag";
		if(!empty($attributes))
			foreach($attributes as $attr => $value)
				$out .= " $attr=\"$value\"";
		if(empty($content)) $out .= " />";
		else $out .= ">$content</$tag>";
		return $out . "\n";
		
	}
	
	/**
	 * Returns a valid doctype definition.
	 * 
	 * Valid doctypes types are:
	 * - html4/strict
	 * - html4/transitional
	 * - html4/frameset
	 * - xhtml/strict
	 * - xhtml/transitional
	 * - xhtml/frameset
	 * - xhtml1.1
	 * - xhtml1.1/basic
	 * - html5
	 * 
	 * @param string $type The doctype to print.
	 * @return void
	 * @since 1.0
	 */
	public static function doctype($type = 'xhtml/transitional') {
		
		static $doctypes = array(
			'html4/strict' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
			'html4/transitional' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
			'html4/frameset' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
			'xhtml/strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
			'xhtml/transitional' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
			'xhtml/frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
			'xhtml1.1' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
			'xhtml1.1/basic' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">',
			'html5' => '<!DOCTYPE HTML>'
		);
		
		if(!isset($doctypes[$type])) $type = 'xhtml/transitional';
		if(strpos($type, 'xhtml') === 0) echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		return $doctypes[$type] . "\n";
		
	}
	
	/**
	 * Returns a valid <html> tag.
	 * 
	 * When $xhtml is true the tag will enclude xmlns, xml:lang attributes.
	 * 
	 * @param bool   $xhtml Indicates whether xhtml specific attributes should be printed.
	 * @param string $lang  The document's language.
	 * @return void
	 * @since 1.0
	 */
	public static function htmlTag($xhtml = true, $lang = 'en') {
		
		$out = '<html ';
		if($xhtml) $out .= 'xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $lang . '" ';
		$out .= 'lang="' . $lang . '">' . "\n";
		return $out;
		
	}
	
	/**
	 * Returns an html link.
	 * 
	 * 
	 */
	public static function link() {}
	
}

?>