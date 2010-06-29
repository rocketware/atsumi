<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

  class validate_MaxChars extends validate_AbstractValidator {
  	private $maxChars = 0;

 	public function __construct($max) {
 		$this->maxChars = $max;
 	}
 	public function validate($data) {

 		if(is_array($data))$data = $data[0];

 		$data = str_replace("\n", " ", str_replace("\r", "", $data));
 		if(empty($data) || strlen($data) <= $this->maxChars)
 			return true;
 		else
 			throw new Exception(sf("You must enter less than %s characters",
 										$this->maxChars
 			));
 	}

 }

?>
