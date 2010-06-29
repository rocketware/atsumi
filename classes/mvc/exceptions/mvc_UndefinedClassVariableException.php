<?php

/**
 * ?Atsumi? Exception class, thrown by a model when an attempt is made to access and
 * undefined class variable.
 * 
 * @author James Oates <james.oates@cteapot.co.uk>
 */
class mvc_UndefinedClassVariableException extends Exception {
	// TODO: Make a atsumi exception!
	
	public function __construct() {
		parent::__construct('Undefined Class Variable');
	}
}
?>