<?php

/**
 *
 * @package Utility
 * @subpackage Calendar
 */
class atsumi_Date {
	
	private $year;
	private $month;
	private $day;

	static public function fromTimestamp ($ts) {
		return new self(date("Y", $ts), date("n", $ts), date("j", $ts));
	}

	static public function fromYmd ($ymd) {
		$matches = null;
		if (! preg_match ('/^ (\\d{4}) - (\\d{2}) - (\\d{2}) $/x', $ymd, $matches)) {
			throw new Exception ('Invalid date format');
		}
		return new self ($matches [1], $matches [2], $matches [3]);
	}

	public function __construct ($year, $month, $day) {
		
		// TODO: check date is valid
		
		$this->year 		= (int) $year;
		$this->month 		= (int) $month;
		$this->day 			= (int) $day;
	}

	public function __toString() {
		return $this->getYmd();
	}
	
	public function getYear () 		{ return $this->year;	}
	public function getMonth () 	{ return $this->month;	} 
	public function getDay () 		{ return $this->day;	}


	public function adjust ($year  = 0, $month = 0, $day = 0) {
		$newDate = self::fromTimestamp(mktime (0,0,0, $this->month+$month, $this->day+$day, $this->year+$year));
		$this->year = $newDate->getYear();
		$this->month = $newDate->getMonth();
		$this->day = $newDate->getDay();
	}

	public function getTimestampDayStart () {
		return mktime (0,0,0, $this->month, $this->day, $this->year);
	}
	public function getTimestampDayEnd () {
		return mktime (0,0,0, $this->month, $this->day + 1, $this->year);
	}
	public function getYmd () {
		return sprintf ('%04d-%02d-%02d', $this->year, $this->month, $this->day);
	}	
}
?>