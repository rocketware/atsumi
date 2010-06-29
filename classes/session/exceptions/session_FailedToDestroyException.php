<?php

class session_FailedToDestroyException extends session_Exception {

	public function __construct() {
		parent::__construct('Session could not be destroyed');
	}
}
?>