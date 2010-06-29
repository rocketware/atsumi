<?php
/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  class validate_Required extends validate_AbstractValidator {
  	
  	private $required;
  	private $errorMessage;
  	
   	public function __construct($required = true, $errorMessage = "This is required") {
 		$this->required = $required;
 		$this->errorMessage = $errorMessage;
 	}
 	public function isRequired() {
 		return $this->required;	
 	}
 	public function validate($data) {
 		if(!$this->required ||(!is_null($data) && strval($data) != "" && !is_array($data)))
 			return true;
 		elseif(is_array($data)) {
 			$empty = true;
 			foreach($data as $item)
 				if(!empty($item)) $empty = false;
 			
 			if($empty) throw new Exception($this->errorMessage);
 		} else throw new Exception($this->errorMessage);
 		
 	}
 		
 	
 }
?>
