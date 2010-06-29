<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class validate_EmailAddress extends validate_AbstractValidator {
  	
	private $testDomain;
	private $arrayIndex = 0;	// default set to 0 for email confirmation's
	
 	public function __construct($testDomain = true, $arrayIndex = null) {
 		$this->testDomain = $testDomain;
 		if(!is_null($arrayIndex)) $this->arrayIndex = $arrayIndex;
 	}
 	
 	
 	public function validate($data) {
 		if(is_array($data)) $data = $data[$this->arrayIndex];
 		if(empty($data) || $this->validEmailAddress($data)) {
 			if($this->testDomain) {
	 			if($this->checkDomainExists($data)) { 
	 				return true;
	 			} else {
	 				throw new Exception("Email address is not valid");
	 			}
 			} else {
 				return true;
 			}
 		} else {
 			throw new Exception("Must be a valid email address");
 		}
 	}
 	
 	
 	private function validEmailAddress($in) {
 	 	return preg_match("|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$|i", $in) ? true : false;
 	}
 	
 	
 	private function checkDomainExists($email) {
 		if(empty($email)) {
 			return true;
 		} else {
	 		list($username,$domain)=explode("@",$email);
	 		#if( getmxrr( $domain, $mxhosts ) == FALSE && gethostbyname($domain) == $domain ) {
	 		if( getmxrr( $domain, $mxhosts ) == FALSE ) {
				return false;
			} else {
				return true;
			}
 		}
 	}
 	
}
?>
