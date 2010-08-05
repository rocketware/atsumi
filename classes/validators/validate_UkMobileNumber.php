<?php
  class validate_UkMobileNumber extends validate_AbstractValidator {

 	public function __construct() {
 	}
 	public function validate($data) {
 		// handle if it's from a confirmation input...
 		if(is_array($data)) $data = $data[0];

 		if(empty($data) || $this->validMobileNumber($data))
 			return true;
 		else throw new Exception("Must be a valid mobile number");

 	}
 	private function validMobileNumber($in) {
 	 	return preg_match("/^((07|00447|\+447|447)[1-9][0-9]{8})$/", $in) ? true : false;
 	}
 }
?>
