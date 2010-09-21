<?php

class widget_AgeRangeElement extends widget_AbstractElement {
	private $options = array();
	private $ageMin = 0;
	private $ageMax = 100;
	private $separator = 'to';

	public function __construct($args) {
		if(isset($args['ageMin'])) $this->ageMin = $args['ageMin'];
		if(isset($args['ageMax'])) $this->ageMax = $args['ageMax'];
		if(isset($args['separator'])) $this->separator = $args['separator'];
	}

	function renderElement() {


		$date = $this->getValue();

		$minValue = is_array($date) && array_key_exists(0,$date)?$date[0]:null;
		$maxValue = is_array($date) && array_key_exists(1,$date)?$date[1]:null;



		$minOptions = sfl("<option value='' >Any</option>");
		for($i = $this->ageMin; $i <= $this->ageMax; $i++) {
			// TODO: Bug here days don't always return the correct number of days...

			if($minValue == $i)
				$minOptions .= sfl("<option value='%s' selected='selected'>%s</option>",$i,$i);
			else
				$minOptions .= sfl("<option value='%s'>%s</option>",$i,$i);

		}

		$maxOptions = sfl("<option value='' >Any</option>");

		for($i = $this->ageMin; $i <= $this->ageMax; $i++) {

			// TODO: Bug here days don't always return the correct number of days...
			if($maxValue == $i)
				$maxOptions .= sfl("<option value='%s' selected='selected'>%s</option>",$i,$i);
			else
				$maxOptions .= sfl("<option value='%s'>%s</option>",$i,$i);

		}

		$out =	sfl("<select name='%s[min]' id='form_%s' class='inputAgeRange inputAgeRangeMin' >%s</select>",
						$this->getName(), $this->getName(), $minOptions
					);

		$out .=	sfl("%s <select name='%s[max]' id='form_%s' class='inputAgeRange inputAgeRangeMax' >%s</select>",
						$this->separator, $this->getName(), $this->getName(), $maxOptions
					);

		return $out;

	}
}

?>