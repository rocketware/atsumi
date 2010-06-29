<?php

class widget_TimeElement extends widget_AbstractElement {
	private $options = array();
	
	protected $miniteInterval;
	protected $hourInterval;

	public function __construct($args) {
		$this->miniteInterval =(isset($args['minuteInterval']) ? $args['minuteInterval'] : 1);
		$this->hourInterval =(isset($args['hourInterval']) ? $args['hourInterval'] : 1);
	}

	function renderElement() {
		$time = $this->getValue();
		$hourValue = $time['hour'];
		$minValue = $time['min'];
		
		
	//	if($elementValue == "" || is_null($elementValue))
		
		$hourOptions = sfl('<option value="">Hour</option>');
		for($i = 0; $i <= 23; $i += $this->hourInterval) {
			$text =($i <= 9 ? "0$i" : $i);
			
			$hourOptions .= sfl(
				"<option value='%s'%s>%s</option>", $i,
				(intval($hourValue) == intval($i) ? ' selected="selected"' : ''),
				$text
			);
		}	
			
		$minOptions = sfl("<option value=''>Minutes</option>");
		for($i = 0; $i <= 59; $i += $this->miniteInterval) {
			$text =($i <= 9 ? "0$i" : $i);
			
			$minOptions .= sfl(
				"<option value='%s'%s>%s</option>", $i,
				(intval($minValue) == intval($i) ? ' selected="selected"' : ''),
				$text
			);
		}	

		$out = sfl(
			"<select name='%s[hour]' id='form_%s' style='width:60px;' >%s</select>", 
			$this->getName(),
			$this->getName(),
			$hourOptions
		);

		$out .=	sfl(
			": <select name='%s[min]' id='form_%s' style='width:90px;' >%s</select>", 
			$this->getName(),
			$this->getName(),
			$minOptions
		);

		return $out;		
	}
}
?>