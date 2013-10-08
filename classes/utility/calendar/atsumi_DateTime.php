<?php

/**
 *
 * @package Utility
 * @subpackage Calendar
 */
class atsumi_DateTime {
	
	const FORMAT_FRIENDLY = "F j, Y, g:i a";
	
	private $timestamp;

	public function __construct ($timestamp) {
		$this->timestamp 	= (int) $timestamp;
	}

	public function __toString() {
		return (String)$this->timestamp;
	}	
	
	public function format($formatString) {
		return date($formatString, $this->timestamp);
	}

}
?>