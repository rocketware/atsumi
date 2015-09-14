<?php

class widget_TextAreaElement extends widget_AbstractElement {

	private $disabled = false;
	private $rows = 7;
	private $placeholder = '';

	public function __construct($args) {
		if(array_key_exists("disabled", $args))
			$this->disabled = $args["disabled"];

		if(array_key_exists('rows', $args))
			$this->rows = $args['rows'];

		if(array_key_exists('placeholder', $args))
			$this->placeholder = $args['placeholder'];
	}

	public function outputSpecific() {
		return array();
	}

	function renderElement() {
		return(	sf("<textarea name='%s' %s %s id='form_%s'%s%s class='inputTextarea'>%s</textarea>",
						$this->getName(),
						($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '',
						strlen($this->placeholder)?sf(' placeholder="%s"', $this->placeholder):'',
						$this->getName(),
						$this->disabled?" disabled='true'":"",
							!is_null($this->rows)?sf(' rows="%s"',$this->rows):'',
						$this->getValue()

					)
				);
	}
}




?>