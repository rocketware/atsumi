<?php
/**
 * File defines all functionaility of the caster_PostgreSQL class.
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Caster class instance specific for PostgreSQL databases.
 * @package		Atsumi.Framework
 * @subpackage	Caster
 * @since		1.0
 */
class caster_PostgreSQLToPhp extends caster_Abstract {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * Character to function casting spec
	 * @var array
	 */
	protected $spec = array(
		'A' => 'sqlArrayOrNull',
		'a' => 'sqlArray',
		'b' => 'boolean',
		'd' => 'date',
		'i' => 'integer',
		's' => 'text',
		'S' => 'textOrNull',
		't' => 'timestampWithTimezone'
	);

	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Casts a string in a PostgreSQL format
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
		return (string)$parser->castString($func_args);
	}


	/**
	 * Casts a variable into a PostgreSQL text
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function text($in) {
		if(!is_string($in)) throw new caster_StrictTypeException('Expected String, received: '.$in.' ('.gettype($in).')');
		return sf("%s", $in);
	}

	/**
	 * Casts a variable into a PostgreSQL text thats accepts NULL values
	 * @param string $in String to be casted or null
	 * @return string Casted string
	 */
	static function textOrNull($in) {
		if (!is_string($in) && !is_null($in)) throw new caster_StrictTypeException('Expected String or Null, received: '.$in.' ('.gettype($in).')');

		if (is_string($in) && strlen($in)) return self::text($in);
		elseif (is_null($in)) return null;
	}

	/**
	 * Casts a variable into a PostgreSQL array
	 * @param array $in Array to be casted
	 * @return string Casted string
	 */
	static function sqlArray($in) {
		
		if (!is_array($in)) throw new caster_StrictTypeException('Expected Array, received: '.$in.' ('.gettype($in).')');
		
		if (is_null($in)) return array();
			
		$in = str_replace(array("{","}"),"", $in);
		$arr = explode(",",$in);
			
		// int
		if ($type == "integer") {
			$newArr = array();
			foreach ($arr as $val)
			$newArr[] = intval($val);
			$arr = $newArr;
		}
		return $arr;
	}
	
	static function sqlArrayOrNull($in) {
		if (!is_array($in) && !is_null($in)) throw new caster_StrictTypeException('Expected Array or Null, received: '.$in.' ('.gettype($in).')');

		if (is_array($in) && count($in)) return self::text($in);
		elseif (is_null($in)) return null;
	}

	/**
	 * Casts a variable into a PostgreSQL boolean
	 * @param bool $in Bool to be casted
	 * @return string Casted string
	 */
	static function boolean($in) {
		if (!is_bool($in)) throw new caster_StrictTypeException('Expected Boolean, received: '.$in.' ('.gettype($in).')');
		return $in;
	}

	/**
	 * Casts a variable into a PostgreSQL integer
	 * @param int $in Int to be casted
	 * @return string Casted string
	 */
	static function integer($in) {
		if (!is_int($in)) throw new caster_StrictTypeException('Expected Integer, received: '.$in.' ('.gettype($in).')');
		return $in;
	}

	static function date($in) {
		throw new caster_StrictTypeException('TODO: date');
	}
	static function timestampWithTimezone($in) {
		throw new caster_StrictTypeException('TODO: timestamp');
	}

	/* DEPRECATED METHODS */
}
?>