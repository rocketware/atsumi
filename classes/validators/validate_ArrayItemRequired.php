<?php
/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  class validate_ArrayItemRequired extends validate_AbstractValidator {
  	private $errorMessage;
  	private $requiredFields;
 	public function __construct($required, $errorMessage = "Fill out all fields") {
 		$this->errorMessage = $errorMessage;
 		$this->requiredFields = $required;
 		
 	}
 	public function validate($arr) {
 		if(empty($arr)) return true;
 		if(!is_array($arr)) throw new Exception("Expected array to validate.");
 		$empty=true;
 		foreach($arr as $item) {
 			if(!empty($item)){
 				$empty=false;
 				break;
 			}
 		}
		if($empty) return true;
 		
 		if(!is_array($arr)) 
 			throw new Exception($this->errorMessage);
 		foreach($this->requiredFields as $key => $value) {
 			if(!array_key_exists($value, $arr) || is_null($arr[$value]) || strval($arr[$value]) == "") throw new Exception($this->errorMessage);
 		}
 		
 	}
 		
 	
 }
?>
