<?php

/**
 *
 * @package Database
 * @subpackage Exception
 */
class db_UnexpectedResultException extends db_Exception {

	protected $extendedInfo;

	public function __construct($extendedInfo) {

		$this->extendedInfo = $extendedInfo;
		parent::__construct('Unexpected result from database query');
	}

	public function getInstructions($contentType) {
		return $this->extendedInfo;
	}
}
?>