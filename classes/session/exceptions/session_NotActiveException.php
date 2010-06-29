<?php

class session_NotActiveException extends session_Exception {

	public function __construct($state) {
		parent::__construct(sf('No session is currently active(%s)', $state));
	}
}
?>