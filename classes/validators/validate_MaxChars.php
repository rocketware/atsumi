<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

  class validate_MaxChars extends validate_AbstractValidator {
  	private $maxChars = 0;
  	private $encoding = 'UTF-8';

 	public function __construct($max, $encoding = null) {
 		$this->maxChars = $max;
 		
 		if (!is_null($encoding))
	 		$this->encoding = $encoding;
 	}
 	public function validate($data) {

 		if(is_array($data))$data = $data[0];

 		$data = str_replace("\n", " ", str_replace("\r", "", $data));
 		if(empty($data) || mb_strlen($data, $encoding) <= $this->maxChars)
 			return true;
 		else
 			throw new Exception(sf("You must enter less than %s characters",
 										$this->maxChars
 			));
 	}

 }

?>