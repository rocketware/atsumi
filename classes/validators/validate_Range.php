<?php
/*
 * Created on 3 Apr 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class validate_Range extends validate_AbstractValidator {
	
	private $min;
	private $max;
	
 	public function __construct($min, $max) {
		$this->min =(int) $min;
		$this->max =(int) $max;
		if($this->min > $this->max) throw new Exception('Invalid range - minimum must not be higher than maximum');
 	}
 	
 	
 	public function validate($data) {
		// Check it's a number
		if(!is_numeric($data)) throw new Exception('Value must be numeric');
		
		// Truncate to integer
		$data =(int) $data;

		// Check in range
		if($data > $this->max) throw new Exception('Value above maximum allowed');
		else if($data < $this->min) throw new Exception('Value below minimum allowed');

		return true;
 	}
 	
}

?>
