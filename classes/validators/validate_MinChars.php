<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

  class validate_MinChars extends validate_AbstractValidator {
  	private $minChars = 0;

 	public function __construct($min) {
 		$this->minChars = $min;
 	}
 	public function validate($data) {

 		if(is_array($data))$data = $data[0];

 		if(empty($data) || strlen($data) >= $this->minChars)
 			return true;
 		else throw new Exception(sf("You must enter %s or more characters",
 										$this->minChars
 									));

 	}


 }
?>
