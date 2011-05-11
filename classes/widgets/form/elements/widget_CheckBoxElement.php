<?php

class widget_CheckBoxElement extends widget_AbstractElement {

	protected $onClick;

	public function __construct($args) {

		if (array_key_exists('onClick', $args) && strlen($args['onClick']))
			$this->onClick = $args['onClick'];
			
	}

	public function getValue() {
		return($this->value == 'on') ? true : false;
	}

	public function setValue($input, $files = null) {
		$this->value =(isset($input[$this->name]) && $input[$this->name] == 'on') ? true : false;
	}
	function renderElement() {
		return(sf("<input type='checkbox' name='%s' %s id='form_%s' class='inputCheckbox' %s />",
							$this->getName(),
							$this->getValue() ? "checked='checked'" : "",
							$this->getName(),
							!is_null($this->onClick) && strlen($this->onClick)?sf(' onClick="%s"', $this->onClick):''
				));
	}
}




?>