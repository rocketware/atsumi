<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

  class validate_RegExpression extends validate_AbstractValidator {

  	const REGEX_MATCH			= 0;
  	const REGEX_NOT_MATCH		= 1;

  	private $regExpression;
  	private $errorMessage 	= "Invalid value";
  	private $direction 		= 0;

 	public function __construct($regExpression, $direction = self::REGEX_MATCH, $errorMessage = null) {

 		$this->regExpression	= $regExpression;
 		$this->direction 		= $direction;

 		if(!is_null($errorMessage)) $this->errorMessage = $errorMessage;

 	}
 	public function validate($data) {
 		if(is_array($data))
 			$data = $data[0];

 		if(empty($data) ||($this->direction == self::REGEX_MATCH && $this->validExpression($data)) ||($this->direction == self::REGEX_NOT_MATCH && !$this->validExpression($data)))
 			return true;
 		else throw new Exception($this->errorMessage);

 	}
 	private function validExpression($in) {
 	 	return preg_match($this->regExpression, $in) ? true : false;
 	}
 }
?>
