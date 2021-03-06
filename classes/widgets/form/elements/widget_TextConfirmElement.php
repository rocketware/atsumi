<?php

class widget_TextConfirmElement extends widget_AbstractElement {

	protected $htmlType 		= 'text';
	protected $confirmText 		= "Please confirm";
	protected $cssClassName 	= 'inputTextConfirm';
	protected $placeholder = '';

	public function __construct($args) {
		if(array_key_exists("confirmText", $args))
			$this->confirmText = $args['confirmText'];

		if (array_key_exists('placeholder', $args) && strlen($args['placeholder']))
			$this->placeholder = $args['placeholder'];

	}

	function renderElement() {

		$value = $this->getValue();
		if(!is_array($value)) $value = array();
		if(!array_key_exists(0,$value)) $value[0] = "";
		if(!array_key_exists(1,$value)) $value[1] = "";

		$out = sf("<input type='%s' name='%s[0]' value='%s' %s id='form_%s' class='%s %sPrimary' %s /><br />",
					$this->htmlType, $this->getName(), parent::makeInputSafe($value[0]), ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', 
					$this->getName(), $this->cssClassName, $this->cssClassName,
					strlen($this->placeholder)?sf(' placeholder="%s"', $this->placeholder):''
				);

		$out .= sf("<label for='form_%s' class='rowLabel f08 rowLabelSecondary'>%s%s</label>",
					$this->getName(), $this->confirmText,
					($this->getRequired() && !$this->getValidates()) ? $this->goAsterisks() : "");

		$out .= sf("<input type='%s' name='%s[1]' value='%s' %s id='form_%s' class='%s %sSecondary' />",
					$this->htmlType, $this->getName(), parent::makeInputSafe($value[1]), ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', 
					$this->getName(), $this->cssClassName, $this->cssClassName
				);

		return $out;
	}

}

?>