<?php
class mvc_DynamicModel extends mvc_AbstractModel {

	protected $structure = array();

	
	/* generic */
	public function __construct ($spec = array()) {
		
		$data = array();
		foreach ($spec as $k => $properties) {
			$this->add($k, $properties);
			
			if (isset($properties['value']))
				$data[$k] = $properties['value'];
			elseif (isset($properties['default']))
				$data[$k] = $properties['default'];
		}
		
		parent::__construct($data);
	}
	
	

	public function add ($key, $properties) {

		$this->structure[$key] = array(
			'type'		=> $properties['type'],
			'create'	=> isset($properties['create'])?$properties['create']:false,
			'structure'	=> isset($properties['structure'])?$properties['structure']:null,
			'model'		=> isset($properties['model'])?$properties['model']:null,
			'default'	=> isset($properties['default'])?$properties['default']:null
		);

		if (isset($properties['default']))
			$this->set($key, $properties['default']);
		
		if (isset($properties['value']))
			$this->set($key, $properties['value']);

	}

	// is defined?
	public function exists ($key) {
		return array_key_exists($key, $this->structure);
	}


}
?>