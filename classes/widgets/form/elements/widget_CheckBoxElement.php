<?php

class widget_CheckBoxElement extends widget_AbstractElement {
	
	
	public function __construct($args) {

	}

	public function getValue() {
		return($this->value == 'on') ? true : false;
	}
	
	public function setValue($input, $files = null) {
		$this->value =(isset($input[$this->name]) && $input[$this->name] == 'on') ? true : false;
	}
	function renderElement() {
		return(sf("<input type='checkbox' name='%s' %s id='form_%s' class='checkbox' />", 
							$this->getName(),
							$this->getValue() ? "checked='checked'" : "",
							$this->getName()
				));
	}	
}




?>