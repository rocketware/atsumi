<?php

abstract class widget_AbstractElement {

	protected $name 			= null;
	protected $label 			= null;
	protected $value 			= null;
	protected $defaultValue		= null;
	protected $forceDefault		= false;
	protected $submitted		= false;
	protected $validates 		= true;
	protected $validators 		= null;
	protected $errors			= array();
	protected $required			= false;
	protected $tabindex			= false;

	protected $cssStyle			= null;
	protected $cssClass			= null;
	
	
	/**
	 * @deprecated
	 */
	protected $style			= null;


//	abstract function outputSpecific();





	/*
	function output() {

		return array_merge(	$this->outputGeneric(),
							$this->outputSpecific());
	}
	protected function outputGeneric() {

		return array(	"name"			=> $this->getName(),
						"value"			=> $this->getValue(),
						"validation"	=> $this->getValidationData()
					);
	}
	*/


	protected function preRender() { }
	protected function postRender() { }

	static public function makeInputSafe($in) {
		$in = str_replace("'", "&#39;", $in);
		$in = str_replace('"', '&quot;', $in);
		return $in;
	}

	public function render($options = array()) {
					
		$out = $this->preRender();
		$out .= sfl('<div class="row%s%s%s row_%s"%s>',
				$this->style ? " " . $this->style : "",
				$this->cssClass ? " " . $this->cssClass : "",
				($this->submitted && !$this->validates) ? " error" : "",
				$this->name,
				$this->cssStyle ? " style='" . $this->cssStyle . "'": ""
			);
		try {
			$out .= sf('%s%s<div class="element">%s</div>', 
				$this->renderErrors(), 
				(($this->label != '' || $this->getRequired()) && 
				(!array_key_exists('label', $options) || $options['label'] !== false) ? 
					$this->renderLabel() : ''), 
				$this->renderElement());
		} catch (Exception $e) {

			// if in debug mode display exception details
			if (atsumi_Debug::getActive())
				$out .= sfl('Element Exception "%s": %s #%s', $e->getMessage(), $e->getFile(), $e->getLine());

			// fire error listeners
			Atsumi::error__listen($e);
		}
		$out .= '</div>';

		$out .= $this->postRender();
		return $out;

	}

	protected function renderErrors() {
		if(!count($this->errors) || !$this->submitted || $this->forceDefault) return;
		$div = "<div class='errors'>";
		$div .= "<ol>";
		foreach($this->errors as $error) {
			$div .= sf("<li>%s</li>", $error);
		}
		$div .= "</ol>";
		$div .= "</div>";
		return $div;

	}

	protected function renderLabel() {
		return sf("<label for='form_%s' class='rowLabel'>%s%s</label>",
					$this->getName(),
					$this->label,
					($this->getRequired() && !$this->getValidates()) ? $this->goAsterisks() : "");
	}

	protected function goAsterisks() {
		return "<strong class='required'>*</strong>";
	}

	public function validate() {
		if(is_null($this->validators)) return true;

		// if the element errored already then don't validate
		// as the data is probably null...
		if(count($this->errors)> 0) {
			$this->validates = false;
			return false;
		}

		foreach($this->validators as $validator) {
			if($validator instanceof validate_Required && $validator->isRequired()) $this->required = true;

			 $elementValue =($this->forceDefault) ? null : $this->getValue();

			if(is_null($elementValue) && !is_null($this->getDefault()))
				$elementValue = $this->getDefault();

			try {
				$validator->validate($elementValue);
			} catch(Exception $e) {
				$this->errors[] = $e->getMessage();
				$this->validates = false;
			}
		}
	}
	protected function getValidationData() {

		if($this->submitted && !$this->forceDefault)
				$validateArr = array(	"validates"		=> $this->validates,
										"errors"		=> $this->errors
									);
		else 	$validateArr = array(	"validates"		=> $this->validates,
										"errors"		=> array());

		return $validateArr;
	}

	function setSubmitted($in) {
		$this->submitted = $in;
	}

	public function setValidators($in) {
		$this->validators = $in;
	}

	public function setName($in) {
		if(!is_string($in)) throw new Exception("Name must be of type String");
		$this->name = $in;
	}

	public function setLabel($in) {
		$this->label = $in;
	}

	public function setError(Exception $e) {
		$this->errors[] = $e->getMessage();
	}

	public function setValue($input, $files = array()) {
		$this->value = isset($input[$this->name]) ? $input[$this->name] : null;
	}

	public function setDefault($in) {
		$this->defaultValue = $in;
	}

	public function setCssClass($in) {
		$this->cssClass = $in;
	}

	public function setCssStyle($in) {
		$this->cssStyle = $in;
	}

	public function setTabindex($in) {
		$this->tabindex = (int) $in;
	}
	public function setForceDefault($in) {
		if(!is_bool($in)) throw new Exception("Force Default must be of type Bool");
		$this->forceDefault = $in;
	}


	public function getName() {
		return $this->name;
	}
	public function getValue() {
		return(is_null($this->value) || $this->getForceDefault()) ?
					$this->defaultValue :
					$this->value;
	}
	public function getValidates() {
		return $this->validates;
	}
	public function getDefault() {
		return $this->defaultValue;
	}
	public function getForceDefault() {
		return $this->forceDefault;
	}
	public function getRequired() {
		return $this->required;
	}

	/**
	 * @deprecated
	 */
	public function setStyle($in) {
		$this->style = $in;
	}

}




?>