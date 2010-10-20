<?php

class validate_IpAddress extends validate_AbstractValidator {
  	
 	public function __construct() {
 	}
 	
 	
 	public function validate($data) {
 		
 		if(is_array($data)) $data = $data[0];
 		
 		if(empty($data) || preg_match("#^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$#", $data)) {
 			return true;
			
 		} else {
 			throw new ValidationException("Invalid IP address");
 		}
 	}
 	 	
}
?>
