<?php

/**
 * Contains the HtmlHelper class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use MVCWebComponents\Register;

/**
 * The HtmlHelper class provides useful, commonly used HTML generation functions.
 * 
 * @version 1.2
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
		if(empty($content) and $tag != 'script') $out .= " />";
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
	 * Returns a valid html link.
	 * 
	 * @param string $url
	 * @param string $text The text to display inside the <a> tags.
	 * @param array  $extra Additional attributes for the tag,
	 * @return string
	 * @since 1.0
	 */
	public static function link($url, $text = '', $extra = array()) {
		
		$url = UrlHelper::fix($url);
		return static::tag('a', $text, array_merge($extra, array('href' => $url)));
		
	}
	
	/**
	 * Returns a valid CSS link.
	 * 
	 * @param string $file
	 * @return string
	 * @since 1.1
	 */
	public static function css($file) {
		
		if(strpos($file, 'http://') !== 0)
			$file = Register::read('env.styles_url') . $file;
		if(substr($file, -4) != '.css')
			$file .= '.css';
		
		return static::tag('link', '',
			array('href' => $file, 'type' => 'text/css', 'rel' => 'stylesheet'));
		
	}
	
	/**
	 * Returns a valid html <img> tag,
	 * 
	 * @param string $img Path to the image.  If not absolute (http://) will prepend the env.img_url.
	 * @param string $alt The text for the imgs alt attribute.
	 * @param array  $extra Additional attributes for the tag.
	 * @return string
	 * @since 1.1
	 */
	public static function img($img, $alt = '', $extra = array()) {
		
		if(strpos($img, 'http://') !== 0)
			$img = Register::read('env.img_url') . $img;
		
		return static::tag('img', '', array_merge($extra, array('src' => $img, 'alt' => $alt)));
		
	}
	
	/**
	 * Returns a valid html <script> tag.
	 * 
	 * Note: this is most appropriate to use for files, if you're writing a 
	 * script inline it would probably be neater in plain HTML.
	 * 
	 * @param string $script
	 * @return string
	 * @since 1.2
	 */
	public static function script($script) {
		
		if(strpos($script, 'http://') !== 0)
			$script = Register::read('env.scripts_url') . $script;
		if(substr($script, -3) != '.js')
			$script = $script . '.js';
		
		return static::tag('script', '', array('src' => $script, 'type' => 'text/javascript'));
		
	}
	
}

?>