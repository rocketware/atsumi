<?php

class widget_RadioElement extends widget_AbstractElement {
	private $options = array();
	private $onChange = null;

	public function __construct($args) {
		$this->options = $args['options'];
		$this->onChange = isset($args['onChange'])?$args['onChange']:null;
	}
	function renderElement() {
		$html = "";
		$elementValue = $this->getValue();
		foreach($this->options as $value => $option) {
			
			$html .= sfl(
				'<div class="radioOption">'.
				'	<input type="radio" name="%s" value="%s" %s %s %s id="form_%s[%s]"  class="inputRadio">'.
				'	<label for="form_%s[%s]">%s</label>'.
				'</div>', 
				$this->getName(), 
				$value,
				$this->onChange?sf(' onchange="%s"', $this->onChange):'',
				strval($elementValue) == strval($value) ? sf('checked="checked"') : '',
				($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', 
				$this->getName(),
				$value, 
				$this->getName(), 
				$value, 
				$option
			);
		}

		return sf("<div class='radioGroup'>%s</div>",$html);

	}
}

?>