<?php

class ValidationException extends Exception {

	public function __construct($message = null, $code = null) {
		parent::__construct('Validation Failed: '.$message, $code);
	}
}