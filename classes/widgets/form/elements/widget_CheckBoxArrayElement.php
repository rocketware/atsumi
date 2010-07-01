<?php

class widget_CheckBoxArrayElement extends widget_AbstractElement {
	private $options = array();
	private $delimiter = "<br />";
	private $sort = false;

	private $default = array();
	
	public function __construct($args) {
		$this->options = $args['options'];
		if(array_key_exists("delimiter", $args))
		$this->delimiter = $args['delimiter'];
		
		if(array_key_exists("default", $args))
		$this->default = $args['default'];
		
		if(array_key_exists("sort", $args))
		$this->sort = $args['sort'];
		
		
	}
	
	function setValue($input, $files = array()) {
		if(empty($input[$this->name])) $input[$this->name] = $this->default;
		// creates an array of ids holding boolean values
		foreach($this->options as $option => $name) {
			if(!isset($input[$this->name][$option]) || !$input[$this->name][$option]) $input[$this->name][$option] = false;
			else $input[$this->name][$option] = true;	
		}		
		$this->value = $input[$this->name];
	}
	
	function renderElement() {
		$out = "";
		$valueArr = $this->getValue();
		
		//sort the options into 3 cols
		if($this->sort) {
			asort($this->options);

		}
		foreach($this->options as $value => $option) {
			$out.=(sf("<div class='checkBoxItem'><input type='checkbox' name='%s[%s]' %s id='form_%s[%s]' class='checkbox' /><label for='form_%s[%s]'>%s</label>%s</div>", 
						$this->getName(),$value,
						(in_array($value, $valueArr) && $valueArr[$value]) ? "checked='checked'" : "",
						$option,$value,
						$option,$value,
						$option, $this->delimiter
					));
		}
		return "<div class='checkBoxArray'>".$out."<br clear='both' /></div>";
	}
	
}

?>