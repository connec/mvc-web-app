<?php

use MVCWebApp\Controller;

class PagesController extends Controller {
	
	public function display($page) {
		
		$this->view = "pages/$page";
		
	}
	
}

?>