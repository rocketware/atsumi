<?php

/**
 *
 * @package Database
 * @subpackage Exception
 */
class db_NoConnectionException extends db_Exception {

	public function __construct() {
		parent::__construct('No Database Connection');
	}
}