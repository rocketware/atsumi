<?php

class widget_TextElement extends widget_AbstractElement {

	protected $htmlType 		= 'text';
	protected $cssClassName 	= 'inputText';
	protected $placeholder		= '';
	protected $onChange			= null;
	protected $onKeydown		= null;
	protected $onFocus			= null;
	protected $onBlur			= null;
	protected $rows;

	public function __construct($args) {
		if (array_key_exists('placeholder', $args) && strlen($args['placeholder']))
			$this->placeholder = $args['placeholder'];
			
		if (array_key_exists('onChange', $args) && strlen($args['onChange']))
			$this->onChange = $args['onChange'];
			
		if (array_key_exists('onKeydown', $args) && strlen($args['onKeydown']))
			$this->onKeydown = $args['onKeydown'];
			
		if (array_key_exists('onFocus', $args) && strlen($args['onFocus']))
			$this->onFocus = $args['onFocus'];
			
		if (array_key_exists('onBlur', $args) && strlen($args['onBlur']))
			$this->onBlur = $args['onBlur'];
			
	}

	function renderElement() {
		return(sf('<input type="%s" name="%s" value="%s" %s id="form_%s" class="%s" %s%s%s%s%s />',
			$this->htmlType,
			$this->getName(),
			parent::makeInputSafe($this->getValue()),
			($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '',
			$this->getName(),
			$this->cssClassName,
			strlen($this->placeholder)?sf(' placeholder="%s"', $this->placeholder):'',
			!is_null($this->onChange) && strlen($this->onChange)?sf(' onChange="%s"', $this->onChange):'',
			!is_null($this->onKeydown) && strlen($this->onKeydown)?sf(' onKeydown="%s"', $this->onKeydown):'',
			!is_null($this->onFocus) && strlen($this->onFocus)?sf(' onFocus="%s"', $this->onFocus):'',
			!is_null($this->onBlur) && strlen($this->onBlur)?sf(' onBlur="%s"', $this->onBlur):''
		));
	}
}

?>