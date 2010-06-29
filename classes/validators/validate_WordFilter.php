<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
  class validate_WordFilter extends validate_AbstractValidator {
  	private $words = array();
  	private $error = "You have entered a restricted word.";
  	
 	public function __construct($words, $errorMessage) {
 		$this->words = $words;
 		$this->error = $errorMessage;
 	}
 	public function validate($data) {
 		$wordString = "";
 		foreach($this->words as $word) 
 			$wordString .=($wordString==""?"\b":"|\b").$word."[[a-z]*\b";
 		
 		if(preg_match("/".$wordString."/i", $data, $matches)) {
 			throw new Exception($this->error);
 			
 		} else 
 			return true;
 	}
 		
 	
 }
?>
