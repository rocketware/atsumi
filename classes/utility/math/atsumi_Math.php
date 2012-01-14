<?php

/**
 *
 * @package Utility
 * @subpackage Math
 */
class atsumi_Math {
		
	static function getSignedInt($in) {
		
	    $intMax = pow(2, 31)-1;
		
	    if ($in > $intMax)  $out = $in - $intMax * 2 - 2;
	    else 		    	$out = $in;
	   
	    return $out;
	}

}
?>