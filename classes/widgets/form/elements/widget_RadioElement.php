<?php

class widget_RadioElement extends widget_AbstractElement {
	private $options = array();

	public function __construct($args) {
		$this->options = $args['options'];
	}
	function renderElement() {
		$html = "";
		$elementValue = $this->getValue();
		foreach($this->options as $value => $option) {
			if(strval($elementValue) == strval($value))
				$html .= sfl('<div class="radioOption"><input type="radio" name="%s" value="%s" %s id="form_%s[%s]" checked="checked" class="inputRadio"><label for="form_%s[%s]">%s</label></div>', $this->getName(), $value, ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', $this->getName(),$value, $this->getName(), $value, $option);
			else
				$html .= sfl('<div class="radioOption"><input type="radio" name="%s" value="%s" %s id="form_%s[%s]"  class="inputRadio"><label for="form_%s[%s]">%s</label></div>', $this->getName(), $value, ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', $this->getName(),$value, $this->getName(), $value, $option);
		}

		return sf("<div class='radioGroup'>%s</div>",$html);

	}
}

?>