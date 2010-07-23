<?php

abstract class parser_Abstract  {
	
	protected $spec = array();
	
	
	protected function numberOfPercents($s) {
		
		if(!is_string($s)) throw new parser_Exception('Parameter $s should be of type string');
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
	public function parseString ($args) {
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
			$params = array_slice($args, $i, $this->numberOfPercents($format));
			$i += count($params);

			$ret[] = $this->parseReal($format, $params);
			
		}
		return implode('', $ret);
	}
	protected function parseReal ($format, $args) {
		
		/* TODO: does it need escapes here? */
		
		$ret = "";
		$pos0 = 0;
		$i = 0;
		while (true) {
			$pos1 = strpos ($format, "%", $pos0);
			
			/* have we finished parsing the string? */
			if ($pos1 === false)
				return $ret . substr ($format, $pos0);
			
			if ($pos1 + 1 >= strlen ($format))
				throw new parser_Exception ("Invalid format string");
			
			$ret .= substr ($format, $pos0, $pos1 - $pos0);
			$ch = substr ($format, $pos1 + 1, 1);
			
			if (array_key_exists($ch, $this->spec) && method_exists($this,$this->spec[$ch])) {
				$methodName = $this->spec[$ch];
				$ret .= $this->$methodName ($args [$i++]);
			} elseif ($ch == '%') {
				$ret .= '%';	 
			} else {
				throw new parser_Exception ("Invalid format string");
			}
			$pos0 = $pos1 + 2;
		}
	}
	
}

?>