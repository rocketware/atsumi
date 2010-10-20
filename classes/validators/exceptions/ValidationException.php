<?php

class ValidationException extends Exception {

	public function __construct($message = null, $code = null) {
		parent::__construct($message, $code);
	}
}