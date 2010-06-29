<?php

/**
 *
 * @package Database
 * @subpackage Exception
 */
class db_ConnectionFailedException extends db_Exception {

	protected $conString;
	protected $extendedInfo;

	public function __construct($type, $extendedInfo) {
		$this->extendedInfo = $extendedInfo;
		parent::__construct(sf('%s: Failed to connection to the database server', $type));
	}

	public function getInstructions($contentType) {
		return $this->extendedInfo;
	}
}
?>