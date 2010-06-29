<?php

class session_HeadersSentException extends session_Exception {

	public function __construct() {
		parent::__construct('Cannot start session headers already sent');
	}
}