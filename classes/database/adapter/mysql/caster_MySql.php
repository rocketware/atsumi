<?php
/**
 * File defines all functionaility of the caster_MySql class.
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
class caster_MySql extends caster_Abstract {

	/**
	 * Character to function casting spec
	 * @var array
	 */
	protected $spec = array(
		'@' => 'tableName',
		'b' => 'boolean',
		'c' => 'character',
		'C' => 'characterVarying',
		'd'	=> 'datetime',
		'D' => 'date',
		'f' => 'float',
		'i' => 'integer',
		'I' => 'integerOrNull',
		'l' => 'literal',
		'n' => 'numeric',
		's' => 'text',
		'S' => 'textOrNull',
		't'	=> 'timestamp',
		'T' => 'time',
		'x' => 'binary',
		'z' => 'interval',
		'Z' => 'intervalOrNull'
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
		return (string)$parser->castString($func_args);
	}

	/**
	 * Casts a variable into a MySql table name
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function tableName($in) {
		return sf('%s', $in);
	}

	/**
	 * Casts a variable into a MySql text
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function text($in) {
		if(!is_string($in)) throw new caster_StrictTypeException('Expected String');
		return sf("CAST('%s' as CHAR)", mysql_escape_string($in));
	}

	/**
	 * Casts a variable into a MySql text thats accepts NULL values
	 * @param string $in String to be casted or null
	 * @return string Casted string
	 */
	static function textOrNull($in) {
		if (!is_string($in) && !is_null($in)) throw new caster_StrictTypeException('Expected String or Null');

		if (is_string($in) && strlen($in)) return self::text($in);
		elseif (is_null($in)) return 'NULL';
	}

	/**
	 * Casts a variable into a MySql character
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function character($in) {
		return self::text($in);
	}

	/**
	 * Casts a variable into a MySql character varying
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function characterVarying($in) {
		return self::text($in);
	}

	/**
	 * Casts a variable into a MySql float
	 * @param float $in Float to be casted
	 * @return string Casted string
	 */
	static function float($in) {
		return sf("CAST('%s' as DECIMAL)", $in);
	}

	/**
	 * Casts a variable into a MySql boolean
	 * @param bool $in Bool to be casted
	 * @return string Casted string
	 */
	static function boolean($in) {
		return sf("%s", $in ? 'true':'false');
	}

	/**
	 * Casts a variable into a MySql integer
	 * @param int $in Int to be casted
	 * @return string Casted string
	 */
	static function integer($in) {
		return sf("CAST('%s' as SIGNED)", intval($in));
	}
	/**
	 * Casts a variable into a MySql integer or Null
	 * @param int $in Int to be casted
	 * @return string Casted string
	 */
	static function integerOrNull($in) {
		if (is_null($in)) return 'NULL';
		return sf("CAST('%s' as SIGNED)", intval($in));
	}

	/**
	 * Casts a variable into a MySql numeric
	 * @param mixed $in Mixed to be casted
	 * @return string Casted string
	 */
	static function numeric($in) {
		return sf("CAST('%s' as SIGNED)", $in);
	}

	/**
	 * Casts a variable into a MySql binary
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function binary($in) {
		return sf("CAST('%s' as BINARY)", pg_escape_bytea($in));
	}

	/**
	 * Casts a variable into a MySql timestamp with timezone
	 * @param int $in Int to be casted
	 * @return string Casted string
	 */
	static function timestamp($in) {
		//TODO: convert datetime to timestamp or leave to user input
		//return sf("'%s'::TIMESTAMP WITH TIME ZONE", mysql_escape_string(gmdate('Y-m-d H:i:s+00', $in)));
		return sf("'%s'", $in);
	}

	/**
	 * Casts a variable into a MySql datetime
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function datetime($in) {
		return sf("CAST('%s' as DATETIME)", $in);
	}
	
	/**
	 * Casts a variable into a MySql date
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function date($in) {
		return sf("CAST('%s' as DATE)", $in);
	}
	
	/**
	 * Casts a variable into a MySql time
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function time($in) {
		return sf("CAST('%s' as TIME)", $in);
	}
	
	/**
	 * Casts a variable into a MySql date
	 * @param string $in String to be casted
	 * @return string Casted string
	 */
	static function interval($in) {
		//TODO: interval?
		return sf("'%s'", $in);
	}
	static function intervalOrNull($in) {
		//TODO: interval or null?
		if (is_null($in)) return 'NULL';
		return sf("'%s'", $in);
	}

	/**
	 * Performs no casting on the variable, leaving it as it is
	 * @param mixed $in mixed to be casted
	 * @return string Unaffected string
	 */
	static function literal($in) {
		return $in;
	}

}
?>