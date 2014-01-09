<?php

/**
 *
 * @package Utility
 * @subpackage Calendar
 */
class atsumi_DateTime {
	
	const FORMAT_FRIENDLY = "F j, Y, g:i a";
	const FORMAT_COMPACT = "d/m/y H:i";
	
	private $timestamp;

	public function __construct ($timestamp) {
		$this->timestamp 	= (int) $timestamp;
	}

	public function __toString() {
		return (String)$this->timestamp;
	}

	public function format($formatString = null) {
		if (is_null($formatString)) $formatString = self::FORMAT_FRIENDLY;
		return date($formatString, $this->timestamp);
	}
	static public function formatFromTimestamp ($timestamp, $formatString = null) {
		$d = new self($timestamp);
		return $d->format($formatString);
	}

}
?>