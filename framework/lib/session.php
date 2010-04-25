<?php

namespace MVCWebApp;

class Session extends \MVCWebComponents\Session {
	
	public static function start() {
		
		parent::start();
		if(static::check('flash.seen')) static::clear('flash');
		elseif(static::check('flash')) static::write('flash.seen', true);
		
	}
	
}

?>