<?php
abstract class mvc_AbstractModel {

	const OUTPUT_FORMAT_ASSOC 		= 1;
	const OUTPUT_FORMAT_STD_CLASS 	= 2;
	const OUTPUT_FORMAT_OBJECT 		= 3;

	/* generic */
	protected $data = array();


	static public function fromArray ($data) {
		return new static ($data);
	}

	/* generic */
	public function __construct ($data = array()) {
		foreach ($this->structure as $k => $properties) {
			$this->set(
				$k, 
				isset($properties['default'])?
					$properties['default']:null,
				true
			);
		}

		// set data
		foreach ($data as $k => $value) {
			$this->set($k, $value);
		}
	}

	function preOutput($outputType)  { }

	

	/* generic */
	function has ($k) {
		 return array_key_exists($k, $this->data) && isset($this->data[$k]);
	}

	/* generic */
	function set ($k, $v, $force = false) {
		if (!$force &&
			(
				isset($this->structure[$k]['write']) &&
				$this->structure[$k]['write'] == false
			)
		)
			throw new Exception ('Column not writable');

		$this->data[$k] = $v;
	}

	function increment ($k, $v) {
		if (!$this->has($k)) throw new Exception('unknown key: '.$k);

		// increment different data types
		switch ($this->structure[$k]['type']) {

			// INTERVAL
			case 'z':
			case 'Z':

				// check if it's null
				if (is_null($this->data[$k]))
					$this->data[$k] = new atsumi_Interval(0);

				$this->data[$k]->add(new atsumi_Interval($v));
				break;

			// NUMERIC
			case 'i':
			case 'I':
			case 'e':
			case 'E':
			case 'n':
			case 'N':
			case 'f':
			case 'F':

				// check if it's null
				if (is_null($this->data[$k]))
					$this->data[$k] = 0;

				$this->data[$k] += $v;
				break;


			default:
				throw new Exception("Unexpected data type to increment %".$this->structure[$k]['type']);

		}
	}

	function getStructure () {
		return $this->structure;
	}

	function __get ($key) {
		return $this->get($key);
	}

	/* generic */
	function get($key, $strict = true) { 
		if (!array_key_exists($key, $this->data))
			throw new Exception ('Unknown model key: '. $key);
		
		return $this->data[$key];
		/*
		try {
			$value = caster_PostgreSqlToPhp::cast(sf('%%%s', $this->structure[$key]['type']), $this->data[$key]);
			return $value;
		} catch (Exception $e) {
			if ($strict) throw $e;
			return null;
		}
		*/
	}

	static function outputItem ($value, $type) {

		// if abstract model then output that
		if ($value instanceof mvc_AbstractModel)
			return $value->output($type);

		// if array itterate through
		elseif (is_array($value)) {
			$arrayOut = array();
			foreach ($value as $item)
				$arrayOut[] = self::outputItem($item, $type);
				return $arrayOut;

		// otherwise just return value
		} else
			return $value;

	}

	function __toString () {
		return pretty($this->output());
	}

	function output ($type = self::OUTPUT_FORMAT_ASSOC) {

		$this->preOutput($type);

		switch ($type) {

			// Native object
			case self::OUTPUT_FORMAT_OBJECT:
				return $this;

			// Associative array
			case self::OUTPUT_FORMAT_ASSOC:
				$out = array();
				foreach($this->data as $key => $value) {
					if (isset($this->structure[$key]['output']) &&
						$this->structure[$key]['output'] == false)
						continue;

					$out[$key] = self::outputItem($value, $type);
				}
				return $out;

			// Std Class
			case self::OUTPUT_FORMAT_STD_CLASS:
				$out = new stdClass();
				foreach($this->data as $key => $value) {
					if (isset($this->structure[$key]['output']) &&
						$this->structure[$key]['output'] == false)
						continue;

					if ($value instanceof mvc_AbstractModel)
						$out[$key] = $value->output($type);
					else
						$out[$key] = $value;
				}
				return $out;
		}

	}
}
?>