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
		if (preg_match ('/(?:(\d+)\sdays\s)?(?:(\d+):)?(?:(\d+):)?(\d+\.\d+)$/x', $pgInterval, $intervalMatches) ||
			preg_match ('/([0-9]{2}):([0-9]{2}):([0-9]{2})$/x', $pgInterval, $intervalMatches)) {
			
			// organise regex matches
			$intervalMatches = array_reverse($intervalMatches);
			array_pop($intervalMatches);
	
			// return instance of self
			$reflect  = new ReflectionClass('atsumi_Interval');
			$intervalInstance = $reflect->newInstanceArgs($intervalMatches);
			return $intervalInstance; 
			
		
		} else
			throw new Exception ('Invalid interval format');

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

	public function add (self $interval) {
		$this->seconds += $interval->inSeconds();
	}

	public function __toString() {
		return strval($this->inSeconds());
	}

	public function inSeconds() {
		return floatval($this->seconds);
	}

	public function inMinutes() {
		return floatval($this->seconds / self::DURATION_MINUTE);
	}

	public function inHours() {
		return floatval($this->seconds / self::DURATION_HOUR);
	}

	public function inDays() {
		return floatval($this->seconds / self::DURATION_DAY);
	}

	public function inYears() {
		return floatval($this->seconds / self::DURATION_YEAR);
	}
	public function getFormatBreakdown() {

		$remainder = $this->inSeconds();

		$years = floor($remainder / self::DURATION_YEAR);
		$remainder -= $years * self::DURATION_YEAR;

		$days = floor($remainder / self::DURATION_DAY);
		$remainder -= $days * self::DURATION_DAY;

		$hours = floor($remainder / self::DURATION_HOUR);
		$remainder -= $hours * self::DURATION_HOUR;

		$minutes = floor($remainder / self::DURATION_MINUTE);
		$remainder -= $minutes * self::DURATION_MINUTE;

		$seconds = floor($remainder);

		return array(
			'years' => $years,
			'days' => $days,
			'hours' => $hours,
			'minutes' => $minutes,
			'seconds' => $seconds,
		);
	}

	public function format($compact = false) {

		$formatComponents = $this->getFormatBreakdown();

		return sf('%s%s%s:%s:%s',
			$formatComponents['years']?sf('%sy ',$formatComponents['years']):'',
			$formatComponents['days']||$formatComponents['days']?sf('%sd ',$formatComponents['days']):'',
			sprintf('%02d', $formatComponents['hours']),
			sprintf('%02d', $formatComponents['minutes']),
			sprintf('%02d', $formatComponents['seconds'])

		);

	}


}
?>