<?php
class mvc_DynamicModel extends mvc_AbstractModel {

	protected $structure = array();

	/* generic */
	public function __construct ($spec = array()) {
		foreach ($spec as $k => $properties) {
			$this->add($k, $properties);
		}
	}

	public function add ($key, $properties) {

		$this->structure[$key] = array(
			'type'		=> $properties['type'],
			'default'	=> isset($properties['default'])?$properties['default']:null
		);

		if (isset($properties['value']))
			$this->set($key, $properties['value']);

	}

	// is defined?
	public function exists ($key) {
		return array_key_exists($key, $this->structure);
	}


}
?>