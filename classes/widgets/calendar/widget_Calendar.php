<?php
	class widget_Calendar {
		
		protected $month;
		protected $year;
		protected $dates;
		protected $monthName;
		protected $firstDayOfMonth;
		protected $class;
		
		static public function getDateRange(ess_Date $firstDate, ess_Date $lastDate) {
		
			$dateArr = array();
			for($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, date("m", mktime(0, 0, 0, $firstDate->month(), $firstDate->day(), $firstDate->year())), date("Y", mktime(0, 0, 0, $firstDate->month(), $firstDate->day(), $firstDate->year()))); $i++) {
				$currentDate = date("Y-m-d", 
										mktime(0, 0, 0, date("m", mktime(0, 0, 0, $firstDate->month(), 1, $firstDate->year())), $i, date("Y", mktime(0, 0, 0, $firstDate->month(), 1, $firstDate->year())))
									);
				$dateArr[$currentDate] = array();
			}
			
			return $dateArr;
			
		}
	 	static function dayName($int) {
			switch($int) {
				case 1:
					return "Mon";
				case 2:
					return "Tue";
				case 3:
					return "Wed";
				case 4:
					return "Thur";
				case 5:
					return "Fri";
				case 6:
					return "Sat";
				case 7:
					return "Sun";	
			}
		}
		
		function __construct($month, $year, $dateArr = array(), $class = null) {
	
			$this->month	= $month;
			$this->year		= $year;
			$this->class	= $class;
			
			$this->monthName 		= date("F", mktime(0, 0, 0, $this->month, 1, $this->year));
			$this->firstDayOfMonth 	= date("N", mktime(0, 0, 0, $this->month, 1, $this->year));

			
			$this->dates = self::getDateRange(
									new ess_Date($this->year, $this->month, 1),
									ess_Date::from_local_ts(strtotime("+1 month",mktime(0,0,0,$this->month, 1, $this->year)))
							);
				
			if(!empty($dateArr)) $this->checkDateArray($dateArr);
			
			$this->dates = array_merge($this->dates, $dateArr);
			$newDateArr = array();
			foreach($this->dates as $date => $args) 
				$newDateArr[] = array_merge($args, array("date"=>$date));
				
			$this->dates = $newDateArr;
			
		}
		
		private function checkDateArray($dateArr) {
			foreach($dateArr as $date => $attributes) {
				if(!preg_match("|^[0-9]{4}-[0-9]{2}-[0-9]{2}$|", $date))
					throw new Exception(sf("Malfromed date array: %s does not match format YYYY-MM-DD", $date));

				if(!array_key_exists($date, $this->dates))
					throw new Exception(sf("Date not found in date range: %s", $date));
					
			}
		}
		public function __toString() {
			$out = "";
			$out .= sfl("<div class='widgetCalendarContainer%s'><table class='widgetCalendar'>", (!is_null($this->class)) ? ' '.$this->class : '');
				$out .= sfl("<tr>");
				$out .= sfl("<td colspan=7 class='month'>%s</td>", $this->monthName);
				$out .= sfl("</tr>");
				$out .= sfl("<tr>");
				for($i = 1; $i <= 7; $i++) 
					$out .= sfl("<th>%s</th>", self::dayName($i));
				$out .= sfl("</tr>");
				
			$daysRendered = 0;
			$inMonth = false;
			while($daysRendered < count($this->dates)) {
				$out .= sfl("<tr>");
				for($i = 1; $i <= 7; $i++) {
					
					if(!$inMonth && $this->firstDayOfMonth == $i) $inMonth = true;

					if($inMonth && array_key_exists($daysRendered, $this->dates)) {
	
						$out .= $this->renderCell($this->dates[$daysRendered]);
						$daysRendered++;
					} else {
						$out .= sfl("<td>&nbsp;</td>");
					}
				}
				$out .= sfl("</tr>");
			}	
			$out .= sfl("</table></div>");
			return $out;
		}
		
		/* this can be extended to color date cells etc */
		protected function renderCell($date) {
			return sfl("<td class='date'>%s</td>", date("d", strtotime($date['date'])));
		}
		
		
	}
?>