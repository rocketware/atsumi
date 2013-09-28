<?php
/**
 * File defines all functionaility of the caster_Abstract class.
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Abstract caster class that all caster children should inherit from.
 * @package		Atsumi.Framework
 * @subpackage	Caster
 * @since		1.0
 */
abstract class caster_Abstract  {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * Character to function casting spec
	 * @var array
	 */
	protected $spec = array();

	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	static public function getSpec() {
		$caster = new static;
		return $caster->spec;
	}
	
	/**
	 * Checks the given string for the number of caster characters.
	 * @param string $s The string to parse
	 */
	protected function numberOfPercents($s) {
		if(!is_string($s)) throw new caster_Exception('Caster expecting format string, received '.gettype($s).': '.$s);
		$pos = 0;
		$count = 0;
		while(true) {
			$pos = strpos($s, '%', $pos);
			if($pos === false) return $count;
			if($pos + 2 > strlen($s)) throw new Exception('Invalid format string');
			if(substr($s, $pos + 1, 1) != "%") $count++;
			$pos += 2;
		}
	}

	/**
	 * Casts args into a given string by the class held caster spec
	 * @param string $string The string to cast
	 * @param mixed $args The args to be parsed into the string
	 * @param mixed $_ Repeated last arg as needed
	 * @return string The casted string
	 */
	public function castString($string, $args = null, $_ = null) {
		// Get args
		$args = func_get_args();

		if(count($args) == 1 && is_array($args[0])) {
			$real_args = $args [0];
		} else if(count($args) == 2 && is_string($args[0]) && is_array($args[1])) {
			$real_args = array_merge(array($args[0]), $args[1]);
		} else {
			$real_args = $args;
		}
		$args = $real_args;

		$ret = array();
		for($i = 0; $i < count($args); ) {
			$format = $args[$i++];
			$params = array_slice($args, $i, $this->numberOfPercents($format));
			$i += count($params);

			$ret[] = $this->castReal($format, $params);
		}
		return implode('', $ret);
	}
	/**
	 * Casts args into a given string/object by the class held caster spec
	 * @param string $string The string to cast
	 * @param mixed $args The args to be parsed into the string
	 * @return string The casted string
	 */
	public function castObject($string, $args = null, $_ = null) {
		// Get args
		$args = func_get_args();
		
		if (count($args[0]) !== 2) throw new caster_Exception('Cast object expects two parameters');
		
		$ch = substr($args[0][0], 1);
		
		if(array_key_exists($ch, $this->spec) && method_exists($this,$this->spec[$ch])) {
			$methodName = $this->spec[$ch];
			return $this->$methodName($args[0][1]);
		} elseif($ch == '%') {
			return '%';
		} else {
			throw new caster_Exception("Caster received unexpected format character: %".$ch);
		}

	}
	
	public function castArray ($params) {
		$format = array_shift ($params);
		return $this->castReal ($format, $params);
	}
	
	public function castArraySets ($args) {
		$ret = array ();
		for ($i = 0; $i < count ($args); ) {
			$format = $args [$i++];
			$params = array_slice ($args, $i, $this->numberOfPercents($format));
			$i += count ($params);
			array_unshift ($params, $format);
			$ret[] =$this->castArray ($params);
		}
		return $ret;
	}

	/**
	 * Casts args into a given string by the class held caster spec
	 * @param string $format The string to cast
	 * @param array $args The args to be parsed into the string
	 * @return string The casted string
	 */
	protected function castReal($format, $args) {
		$format = $this->formatNewLines($format);

		$ret = "";
		$pos0 = 0;
		$i = 0;
		while (true) {
			$pos1 = strpos($format, "%", $pos0);

			/* have we finished parsing the string? */
			if($pos1 === false)
				return $ret . substr($format, $pos0);

			if($pos1 + 1 >= strlen($format))
				throw new caster_Exception("Invalid format string");

			$ret .= substr($format, $pos0, $pos1 - $pos0);
			$ch = substr($format, $pos1 + 1, 1);

			if(array_key_exists($ch, $this->spec) && method_exists($this,$this->spec[$ch])) {
				$methodName = $this->spec[$ch];
				$ret .= $this->$methodName($args[$i++]);
			} elseif($ch == '%') {
				$ret .= '%';
			} else {
				throw new caster_Exception("Caster received unexpected format character: %".$ch);
			}
			$pos0 = $pos1 + 2;
		}
	}

	/**
	 * Enter function description...
	 * TODO: Possible use PHP_EOL
	 * @param string $str ??
	 * @return string ???
	 */
	function formatNewLines($str) {
		$matches = null;
		preg_match_all('/ [^\\\\] | \\\\ . | \\\\ /x', $str, $matches);
		$ret = '';
		foreach ($matches [0] as $match) {
			if ($match == '\\')
				throw new caster_Exception('Illegal input (lone backslash at end)');
			if ($match {0} == '\\') {
				switch ($match {1}) {
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
					throw new caster_Exception('Illegal escape character: ' . $match {1});
				}
			} else {
				$ret .= $match;
			}
		}
		return $ret;
	}

	/* DEPRECATED METHODS */
}

?>