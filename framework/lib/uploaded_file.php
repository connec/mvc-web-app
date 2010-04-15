<?php

/**
 * Contains the UploadedFile class.
 * 
 * @package mvc-web-app
 * @author Chris Connelly
 */
namespace MVCWebApp;
use MVCWebComponents\BadArgumentException;

/**
 * The UploadedFile class provides a pleasant interface for handling uploaded files.
 * 
 * IMPORTANT: Within the framework this class is initialized from UNFILTERED $_FILES 
 * data, there are/will be functions within the class for checking/validating a file 
 * but it is important to review these (especially file names) as they are untrusted.
 * 
 * @version 0.1
 */
class UploadedFile {
	
	/**
	 * The name of the file as reported by the browser.
	 * 
	 * Do not trust the default value as it is assigned unfiltered from $_FILES 
	 * data which is itself comes from the browser.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $name;
	
	/**
	 * The MIME type of the file as reported from (I think) the browser.
	 * 
	 * The default value should be treated with caution as it is likely set to 
	 * by the browser.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $type;
	
	/**
	 * The temporary location of the uploaded file.
	 * 
	 * The default value for this should NOT be changed as this is set by PHP 
	 * and cannot be affected by the client.
	 * 
	 * @var string
	 * @since 0.1
	 */
	protected $tmp_name;
	
	/**
	 * The error constant of the file upload.
	 * 
	 * @var int
	 * @since 0.1
	 */
	protected $error;
	
	/**
	 * The size of the uploaded file.
	 * 
	 * This is also set by PHP and so can be trusted.
	 * 
	 * @var int
	 * @since 0.1
	 */
	protected $size;
	
	/**
	 * Initialize the file with it's $_FILES data.
	 * 
	 * @param array $details
	 * @return void
	 * @since 0.1
	 */
	public function __construct($details) {
		
		// Check the array.
		foreach(array('name', 'type', 'tmp_name', 'error', 'size') as $field) {
			if(!isset($details[$field]))
				throw new BadArgumentException(
					'UploadedFile::__construct() expects parameter one to be an array 
					containing all the keys "name", "type", "tmpName", "error" and "size".  
					Given:' . print_r($details, true));
			else $this->{$field} = $details[$field];
		}
		
	}
	
}

?>