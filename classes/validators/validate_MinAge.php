<?php

class validate_MinAge extends validate_AbstractValidator {

	protected $minAge;

	public function __construct($minAge) {
		$this->minAge = $minAge;
	}

	public function validate($data) {

		if($this->ageDifference($data) < $this->minAge)
			throw new Exception(sf('You must be older than %s years old', $this->minAge));
	}

	public function ageDifference($data) {
		$yearDiff  = date('Y') - (int)$data['year'];
		$monthDiff = date('n') - (int)$data['month'];
		$dayDiff   = date('j') - (int)$data['day'];

		if($dayDiff < 0 || $monthDiff < 0)
			$yearDiff--;

		return $yearDiff;
	}
}
?>