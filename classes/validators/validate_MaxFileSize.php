<?php
 
  class validate_MaxFileSize extends validate_AbstractValidator {

  	private $maxFileSize;
  	
 	public function __construct($maxSizeMb) {
 		$this->maxFileSize = round($maxSizeMb * 1024 * 1024);
 	}
 	
 	public function validate($data) {
 		if(empty($data) || $data['size'] <= $this->maxFileSize)
 			return true;
 		else throw new Exception(sf("The file uploaded is greater than %s mb", round($this->maxFileSize/1024/1024,2)));
 		
 	}
 		
 	
 }
?>
