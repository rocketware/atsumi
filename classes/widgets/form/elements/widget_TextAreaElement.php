<?php

class widget_TextAreaElement extends widget_AbstractElement {

	private $disabled = false;
	private $rows = 7;

	public function __construct($args) {
		if(array_key_exists("disabled", $args))
			$this->disabled = $args["disabled"];

		if(array_key_exists('rows', $args))
			$this->rows = $args['rows'];
	}

	public function outputSpecific() {
		return array();
	}

	function renderElement() {
		return(	sf("<textarea name='%s' id='form_%s'%s%s >%s</textarea>",
						$this->getName(),
						$this->getName(),
						$this->disabled?" disabled='true'":"",
							!is_null($this->rows)?sf(' rows="%s"',$this->rows):'',
						$this->getValue()

					)
				);
	}
}




?>