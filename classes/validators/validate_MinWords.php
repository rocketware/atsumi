<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
  class validate_MinWords extends validate_AbstractValidator {
  	private $minWords = 0;
  	
 	public function __construct($min) {
 		$this->minWords = $min;
 	}
 	public function validate($data) {
 	
 		if(empty($data) || str_word_count($data) >= $this->minWords)
 			return true;
 		else throw new Exception(sf("You must enter %s or more words",
 										($this->minWords)
 									));
 		
 	}
 		
 	
 }
?>
