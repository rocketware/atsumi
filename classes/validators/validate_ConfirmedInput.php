<?php
/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  class validate_ConfirmedInput extends validate_AbstractValidator {
  	private $errorMessage;
  	
 	public function validate($arr) {
 		
 		if(!is_array($arr) ||($arr[0] != $arr[1])) 
 			throw new Exception("The two fields don't match");
	
 	}
 		
 	
 }
?>
