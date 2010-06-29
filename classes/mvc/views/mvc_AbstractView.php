<?php


abstract class mvc_AbstractView {
	
	protected $data;

	abstract function render();
	
	function __construct($data) {
		$this->data = $data;
	}	
	
	// headers
	public function setHeaders() {
		header(sf('Content-Type: text/html; charset=%s', $this->getCharset()));
	}	
	public function getCharset() {
		return 'utf-8';
	}

	
	//loads a block of text could be a template / snippit
	protected function block($file) {
		return file_get_contents(getcwd() . "/classes/views/blocks/".$file);
	}
	
	//fills the users vars in a template
	protected function populateTemplate($dataArr, $template) {
		foreach($dataArr as $index => $data) {
			if(is_string($data))
				$template = str_replace("##{$index}##", $data, $template);
		}
		return $template;
 	}
 	private function get($idx) {
 		if(array_key_exists($idx, $this->data))
 			return $this->data[$idx];
 //		else throw new Exception(sf("View referenced undefined data : %s", $idx));
 		// commented the below out as could cause bugs... will see how annoying above gets
 		return null;
 	}
 	public function __get($name) {
		$matches = null;
		if(preg_match('/^get_(.+)$/', $name, $matches)) {
			return call_user_func(array($this, 'get'), $matches [1]);
		}
		throw new Exception('Undefined method:' . $name);
	}	
 	
}



?>