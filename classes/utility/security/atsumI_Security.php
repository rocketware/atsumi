<?php

class atsumi_Security {
	
	// returns a number based on a string at a certain length
	// useful for verification codes
	static public function numericCode ($ref, $salt, $length) {
		
		// seed random number generator
		srand(crc32(sf('%s:%s',$ref,$salt)));
		
		// generate the number
		$code = rand(pow(10,($length-1)), pow(10,$length)-1);
		return $code;	
	}
	
}




?>