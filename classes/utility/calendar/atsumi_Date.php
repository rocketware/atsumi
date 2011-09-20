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
	
	public function getTimestampDayStart () {
		return mktime ($this->year, $this->month, $this->day, 0, 0, 0);
	}
	public function getTimestampDayEnd () {
		return mktime ($this->year, $this->month, $this->day + 1, 0, 0, 0);
	}
	public function getYmd () {
		return sprintf ('%04d-%02d-%02d', $this->year, $this->month, $this->day);
	}	
}
?>