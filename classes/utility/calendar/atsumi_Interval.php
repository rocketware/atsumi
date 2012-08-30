<?php

/**
 *
 * @package Utility
 * @subpackage Calendar
 */
class atsumi_Interval {
	

	const DURATION_SECOND 	= 1;
	const DURATION_MINUTE 	= 60;
	const DURATION_HOUR 	= 3600;
	const DURATION_DAY 		= 86400;
	const DURATION_WEEK 	= 604800;
	const DURATION_YEAR 	= 31536000;


	private $seconds 		= 0.0;

	// accepts a postgresql interval string
	static public function fromPostgresql ($pgInterval) {

		$intervalMatches = null;
		// regex to extrat itnerval compoenents - handle invald format
		if (! preg_match ('/(?:(\d+)\sdays\s)?(?:(\d+):)?(?:(\d+):)?(\d+\.\d+)$/x', $pgInterval, $intervalMatches))
			throw new Exception ('Invalid interval format');
		
		// organise regex matches
		$intervalMatches = array_reverse($intervalMatches);
		array_pop($intervalMatches);

		// return instance of self
		$reflect  = new ReflectionClass('atsumi_Interval');
		$intervalInstance = $reflect->newInstanceArgs($intervalMatches);
		return $intervalInstance; 

	}

	public function __construct ($seconds, $minutes = 0, $hours = 0, $days = 0, $years = 0) {
		
		// TODO: check date is valid
		$this->seconds = floatval(
			floatval($seconds) + 
			(intval($minutes) * self::DURATION_MINUTE) + 
			(intval($hours) * self::DURATION_HOUR) + 
			(intval($days) * self::DURATION_DAY) + 
			(intval($years) * self::DURATION_YEAR)
		);
	}

	public function __toString() {
		return strval($this->seconds);
	}

}
?>