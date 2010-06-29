<?php

/**
 *
 * @package Database
 * @subpackage Exception
 */
class db_QueryFailedException extends db_Exception {

	protected $extendedInfo;

	public function __construct($extendedInfo) {

		$this->extendedInfo = $extendedInfo;
		parent::__construct('Database query failed');
	}

	public function getInstructions($contentType) {
		return $this->extendedInfo;
	}
}
?>