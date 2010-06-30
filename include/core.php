<?php
	/*
	 * Atsumi core functions
	 *
	 */
/*

// sprintf shorthand
function sf($args_etc) {
	$args = func_get_args();

	//print_r(call_user_func_array('sprintf', $args));
	if(is_array($args)) return call_user_func_array('sprintf', $args);
	return call_user_func('sprintf', $args);
}
function sfl($args_etc) {

	$args = func_get_args();

	return sf($args)."\n";
}

// prints sprintf
function pf($args_etc) {
	$args = func_get_args();
	print(sf 	($args));
}
function pfl($args_etc) {
	$args = func_get_args();
	print(sfl($args));
}
*/
/*
// prints & flushes sprintf
function ff() {	print(sf 	(func_get_args())); flush();	}
function ffl() {	print(sfl 	(func_get_args())); flush();	}
*/
// handy funcs to handle nulls

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














function quotef_special($args) {
	$ret = array();
	for($i = 0; $i < count($args); ) {
		$format = $args [$i++];
		$params = array_slice($args, $i, $this->num_percents($format));
		$i += count($params);
		array_unshift($params, $format);
		$ret[] = call_user_func_array(array($this, 'quotef'), $params);
	}
	return $ret;
}

function sfl($format_etc) {
	$args = func_get_args();
	return(sf($args)."\n");
}

function sf($format_etc) {
	//print("<pre>");print_r($format_etc);print("</pre>");
	$args = func_get_args();
	return implode('', call_user_func_array('sf_special', $args));
}

function sf_special($format_etc) {

	// get args
	$args = func_get_args();
	if(count($args) == 1 && is_array($args [0])) {
		$real_args = $args [0];
	} else if(count($args) == 2 && is_string($args [0]) && is_array($args [1])) {
		$real_args = array_merge(array($args [0]), $args [1]);
	} else {
		$real_args = $args;
	}
	$args = $real_args;

	$ret = array();
	for($i = 0; $i < count($args); ) {
		$format = $args [$i++];
		$params = array_slice($args, $i, sf_num_percents($format));
		$i += count($params);
		$ret[] = sf_real($format, $params);
	}
	return $ret;
}

function sf_num_percents($s) {
	if(!is_string($s)) throw new Exception('Parameter $s should be of type string');
	$pos = 0;
	$count = 0;
	while(true) {
		$pos = strpos($s, '%', $pos);
		if($pos === false) return $count;
		if($pos + 2 > strlen($s))
			throw new Exception('Invalid format string');
		if(substr($s, $pos + 1, 1) != "%")
			$count++;
		$pos += 2;
	}
}

function sf_real($format, $args) {
	$format = sf_escapes($format);
	$ret = "";
	$pos0 = 0;
	$i = 0;
	while(true) {
		$pos1 = strpos($format, '%', $pos0);
		if($pos1 === false)
			return $ret . substr($format, $pos0);
		if($pos1 + 2 > strlen($format))
			throw new Exception('Invalid format string');
		$ret .= substr($format, $pos0, $pos1 - $pos0);
		$ch = substr($format, $pos1 + 1, 1);
		switch($ch) {

			case 's':
				$ret .= $args [$i++];
				$pos0 = $pos1 + 2;
				break;

			case 'd':
				$ret .= sprintf('%d', $args [$i++]);
				$pos0 = $pos1 + 2;
				break;

			case 'h':
				$ret .= htmlspecialchars($args [$i++], ENT_COMPAT, 'UTF-8');
				$pos0 = $pos1 + 2;
				break;

			case 'H':
				$ret .= nl2br(htmlspecialchars($args [$i++], ENT_COMPAT, 'UTF-8'));
				$pos0 = $pos1 + 2;
				break;

			case 'j':
				$ret .= js_escape($args [$i++]);
				$pos0 = $pos1 + 2;
				break;

			case 'u':
				$ret .= urlencode($args [$i++]);
				$pos0 = $pos1 + 2;
				break;

			case 'U':
				$ret .= urldecode($args [$i++]);
				$pos0 = $pos1 + 2;
				break;
			case '{':
				$s = $args [$i++];
				$pos2 = strpos($format, '}', $pos1 + 2);
				if($pos2 == false)
					throw new Exception('Invalid format string');
				$bits = substr($format, $pos1 + 2, $pos2 - $pos1 - 2);
				for($j = strlen($bits) - 1; $j >= 0; $j--)
					$s = sf_real("%" . substr($bits, $j, 1), array($s));
				$ret .= $s;
				$pos0 = $pos2 + 1;
				break;

			case '%':
				$ret .= '%';
				$pos0 = $pos1 + 2;
				break;

			default:
				throw new Exception(sf('Unrecognised format char: %s(%s)', $ch, $format));
		}
	}
}

function sf_escapes($str) {
	$matches = null;
	preg_match_all('/ [^\\\\] | \\\\ . | \\\\ /x', $str, $matches);
	$ret = '';
	foreach($matches [0] as $match) {
		if($match == '\\')
			throw new Exception('Illegal input(lone backslash at end)');
		if($match {0} == '\\') {
			switch($match {1}) {
			case '_':
				$ret .= '\\';
				break;
			case 'n':
				$ret .= "\n";
				break;
			case 'r':
				$ret .= "\r";
				break;
			default:
				throw new Exception('Illegal escape character: ' . $match {1} .' in string: <pre>'.$str.'</pre>');
			}
		} else {
			$ret .= $match;
		}
	}
	return $ret;
}

function js_escape($str) {
	return str_replace(
		array('\\', '\'', '"', '/', "\n", "\r"),
		array('\\\\', '\\\'', '\\"', '\\/', '\\n', '\\r'),
		$str);
}
/**
 * Calls sf to construct a string from a format string then prints it.
 */
function pfl($format_etc) {
	$args = func_get_args();
	print(sf($args)."\n");
}

/**
 * Calls sf to construct a string from a format string then prints it.
 */
function pf($format_etc) {
	$args = func_get_args();
	print(sf($args));
}

/**
 * Calls pf to print the format string then flushed output.
 */
function ff($format_etc) {
	$args = func_get_args();
	print(sf($args));
	flush();
}

?>