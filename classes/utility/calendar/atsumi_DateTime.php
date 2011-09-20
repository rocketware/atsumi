<?php

/**
 *
 * @package Utility
 * @subpackage Calendar
 */
class atsumi_DateTime {
	
	private $timestamp;

	public function __construct ($timestamp) {
		$this->timestamp 	= (int) $timestamp;
	}

	public function __toString() {
		return $this->timestamp;
	}	

}
?>