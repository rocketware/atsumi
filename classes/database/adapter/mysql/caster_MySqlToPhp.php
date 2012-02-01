<?php
/**
 * File defines all functionaility of the caster_MySqlToPhp class.
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Caster class instance specific for MySql databases.
 * @package		Atsumi.Framework
 * @subpackage	Caster
 * @author		Chris Chrisostomou
 * @since		1.0
 */
class caster_MySqlToPhp extends caster_Abstract {

	/**
	 * Character to function casting spec
	 * @var array
	 */
	protected $spec = array(
		'b' => 'boolean',
		'd' => 'datetime',
		'D' => 'datetimeOrNull',
		'e'	=> 'date', //TODO: decide on letter for date in mysql
		'E'	=> 'dateOrNull',
		'i' => 'integer',
		'I' => 'integerOrNull',
		'f' => 'float',
		'F' => 'floatOrNull',
		's' => 'text',
		'S' => 'textOrNull',
		't' => 'timestamp',
		'T' => 'timestampOrNull',
		'u'	=> 'time', //TODO: decide in letter for time in mysql
		'U'	=> 'timeOrNull'
	);

	/**
	 * Casts a string in a MySql format
	 * NOTE: Annoyance due to PHP scope issue
	 * @param string $string The string to cast
	 * @param mixed $args The args to be parsed into the string
	 * @param mixed $_ Repeated last arg as needed
	 * @return string The casted string
	 */
	static function cast($string, $args = null, $_ = null) {
		$parser =  new self();

		/* 'func_get_args' cannot be called as function arg pre PHP5 */
		$func_args = func_get_args();
		return $parser->castObject($func_args);
	}


	/**
	 * Casts a variable into a MySql text
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function text($in) {
		if(!is_string($in)) throw new caster_StrictTypeException('Expected String, received: '.$in.' ('.gettype($in).')');
		return sf("%s", $in);
	}

	/**
	 * Casts a variable into a MySql boolean
	 * @param bool $in Bool to be casted
	 * @return string Casted string
	 */
	static function boolean($in) {
		if ($in==='0') $in = false;
		if ($in==='1') $in = true;
		if (!is_bool($in)) throw new caster_StrictTypeException('Expected Boolean, received: '.$in.' ('.gettype($in).')');
		return $in;
	}

	/**
	 * Casts a variable into a MySql integer
	 * @param int $in Int to be casted
	 * @return string Casted string
	 */
	static function integer($in) {
		if (!is_int(intval($in))) throw new caster_StrictTypeException('Expected Integer, received: '.$in.' ('.gettype($in).')');
		return intval($in);
	}
	
	static function integerOrNull($in) {
		if (is_null($in)) return null;
		if (!is_int(intval($in))) throw new caster_StrictTypeException('Expected Integer or Null, received: '.$in.' ('.gettype($in).')');
		return intval($in);
	}
	
	static function float($in) {
		if (!is_numeric($in)) throw new caster_StrictTypeException('Expected Float, received: '.$in.' ('.gettype($in).')');
		setType($in, 'float');
		return $in;
	}
	
	static function floatOrNull($in) {
		if (is_null($in)) return null;
		if (!is_numeric($in)) throw new caster_StrictTypeException('Expected Float or Null, received: '.$in.' ('.gettype($in).')');
		setType($in, 'float');
		return $in;
	}

	static function datetime($in) {
		#return atsumi_Date::fromYmd($in);
		return new atsumi_DateTime(strtotime($in));
	}	
	
	static function datetimeOrNull($in) {
		if (is_null($in)) return null;
		return new atsumi_DateTime(strtotime($in));
	}
	
	static function timestamp($in) {
		return new atsumi_DateTime(strtotime($in));
	}
	
	static function timestampOrNull($in) {
		if (is_null($in)) return null;
		return self::timestamp($in);
	}
	
	static function date($in) {
		return atsumi_Date::fromYmd($in);
	}
	
	static function time($in) {
		if (is_null($in)) return null;
		//TODO: time stuff
		return $in;
	}
	
	static function timeOrNull($in) {
		//TODO: time (or null) stuff
		if (is_null($in)) return null;
		return $in;
	}
}
?>
