<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

  class validate_MobileNumber extends validate_AbstractValidator {

 	public function __construct() {
 	}
 	public function validate($data) {
 		// handle if it's a confirmation...
 		if(is_array($data)) $data = $data[0];

 		if(empty($data) || $this->validMobileNumber($data))
 			return true;
 		else throw new Exception("Must be a valid mobile number");

 	}
 	private function validMobileNumber($in) {
 	 	return preg_match("^((07|00447|\+447|447)[1-9][0-9]{8})$", $in) ? true : false;
 	}
 }
?>
