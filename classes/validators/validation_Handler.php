<?php

class validation_Handler {
	
	// validate a value for an array of validators
	static public function validate ($value, array $valiators) {
		
		$validates = true;
		$errors = array();

		foreach($valiators as $validator) {
			try {
				$validator->validate($value);
			} catch(Exception $e) {
				$errors[] = $e->getMessage();
				$validates = false;
			}
		}

		return array (
			"validates" => $validates,
			"errors"	=> $errors
		);
	}




}

?>