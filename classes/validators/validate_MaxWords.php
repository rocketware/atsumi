<?php

/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
  class validate_MaxWords extends validate_AbstractValidator {
  	private $maxWords = 0;
  	
 	public function __construct($max) {
 		$this->maxWords = $max;
 	}
 	public function validate($data) {
 	
 		if(empty($data) || str_word_count($data) <= $this->maxWords)
 			return true;
 		else throw new Exception(sf("You must enter %s words or less.",
 										($this->maxWords)
 									));
 		
 	}
 		
 	
 }
?>
