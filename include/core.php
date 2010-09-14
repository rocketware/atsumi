<?php
	/*
	 * Atsumi core functions
	 *
	 */

function functions_exist($function_name, $_ = null) {
	$functions = func_get_args();

	foreach($functions as $function)
		if(!is_string($function) || !function_exists($function))
			return false;

	return true;
}

function nullif($a, $b) {
	return $a === $b? null : $a;
}
function nullIfNot($a, $b) {
	return $a === $b? $a : null;
}

// strict types...

function strictInt($input) {
	if(is_int($input)) return $input;
	if(is_string($input) && preg_match('/^-?[0-9]+$/', $input)) return(int) $input;
	throw new StrictCastException("int", $input);
}

function strictString($input) {
	if(is_string($input)) return $input;
	if(is_int($input)) return(string) $input;
	throw new StrictCastException("string", $input);
}


// quick compression

function bundle($data) {
	return base64_encode(gzdeflate(serialize($var)));
}
function unbundle($data) {
	return unserialize(gzinflate(base64_decode($data)));
}

function dump($var) {
	pf('<pre>%s</pre>',  htmlspecialchars(pretty($var)));
}

function pretty($value, $prefix = '') {
	if(is_null($value)) return 'null';
	if(is_string($value)) return '\''
		. str_replace(array('\'', "\n", "\r", "\t"), array('\\\'', '\n', '\r', '\t'), $value)
		. '\'';
	if(is_int($value)) return(string) $value;
	if(is_double($value)) return(string) $value;
	if(is_bool($value)) return $value? 'true' : 'false';
	if(is_array($value)) {
		$ret = 'array(';

		if(count($value) <= 0)
			return $ret.')';

		foreach($value as $a => $b)
			$ret .= "\n  " . $prefix . pretty($a) . ': ' . pretty($b, '  ' . $prefix);
		$ret .= "\n".$prefix.')';
		return $ret;
	}
	if(is_object($value)) {
		$ret='';
		if(method_exists($value,'toString'))
			$ret = $value->toString();

		$ret = $ret.' '.get_class($value) . '(';

		if(method_exists($value, 'dumpDebug'))
			$value = $value->dumpDebug();

		foreach($value as $a => $b)
			$ret .= "\n  " . $prefix . $a . ': ' . pretty($b, '  ' . $prefix);
		$ret .= "\n ".$prefix.')';
		return $ret;
	}
	return '?' . gettype($value) . '?';
}


/**
 * This just reorders the arguments to PHP's builtin checkdate().
 */
function checkdatez($year, $month, $day) {
	return checkdate($month, $day, $year);
}

/**
 * This just reorders the arguments to PHP's builtin mktime().
 */
function mktimez($year, $month, $day, $hour, $minute, $second) {
	return mktime($hour, $minute, $second, $month, $day, $year);
}


/* String manipulation short hand */

function sf ($args) {
	$args = func_get_args();
	return call_user_func_array(
		array('caster_Web','cast'),
		$args);
}
function sfl ($args) {
	$args = func_get_args();
	return (call_user_func_array('sf',$args).PHP_EOL);
}
function pf ($args) {
	$args = func_get_args();
	print (call_user_func_array('sf',$args));
}
function pfl ($args) {
	$args = func_get_args();
	print (call_user_func_array('sf',$args).PHP_EOL);
}
function ff ($args) {
	$args = func_get_args();
	print (call_user_func_array('sf',$args));
	flush();
}

?>