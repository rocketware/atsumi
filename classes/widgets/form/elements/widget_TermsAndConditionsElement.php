<?php

class widget_TermsAndConditionsElement extends widget_AbstractElement {

	protected $checkboxText = 'I have read and understand the above.';
	protected $termsText 	= '';

	public function __construct($args) {
		if(array_key_exists('terms', $args))
			$this->termsText = $args['terms'];
	}

	public function getValue() {
		return($this->value == 'on') ? true : false;
	}

	public function setValue($input, $files = null) {
		$this->value =(isset($input[$this->name]) && $input[$this->name] == 'on') ? true : false;
	}

	public function render() {
		$out = $this->preRender();
		$out .= sfl('<div class="row%s%s">%s</div>',
						$this->style ? " " . $this->style : "",
						($this->submitted && !$this->validates) ? " error" : "",
						$this->renderElement());
		$out .= $this->postRender();
		return $out;

	}
	function renderElement() {
		return(sf("<div class='inputTermsContainer'><div class='inputTermsText'>%s</div>%s<div class='inputTerms'><input type='checkbox' name='%s' %s %s id='form_%s' class='inputTermsCheckbox' /> %s</div></div>",
							$this->termsText,
							$this->renderErrors(),
							$this->getName(),
							$this->getValue() ? "checked='checked'" : "",
							($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '',
							$this->getName(),
							$this->renderLabel()
				));
	}
}




?>