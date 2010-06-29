<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
  class validate_ArrayMaxChars extends validate_AbstractValidator {
  	private $maxChars = 0;
  	private $requiredFields = array();
  	
 	public function __construct($requiredFields, $max) {
 		$this->maxChars = $max;
 		$this->requiredFields = $requiredFields;
 	}
 	public function validate($arr) {
 	 	if(empty($arr)) return true;
 	 	
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
 			if(strlen($arr[$value]) > $this->maxChars) throw new Exception(sf("You have entered: '<em>%s</em>'  this is over the allowed %s characters.",
 										$arr[$value], $this->maxChars));
 		}
 		
 	/*
 		
 		if(empty($data) || strlen($data) < $this->maxChars)
 			return true;
 		else 
 			throw new Exception(sf("You must enter less than %s characters",
 										$this->maxChars
 								));
 		*/
 	}
 		
 	
 }
?>
