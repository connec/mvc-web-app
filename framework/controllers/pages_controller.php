<?php

use MVCWebApp\Controller,
	MVCWebComponents\Inflector,
	MVCWebComponents\Register;

class PagesController extends Controller {
	
	public function display($page) {
		
		$this->view = "$page";
		
		$page = Inflector::titleize($page);
		$this->set('page_title', "$page :: " . Register::read('app_name'));
		$this->set('page_heading', $page);
		
	}
	
}

?>