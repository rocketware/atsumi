<?php

class widget_TextElement extends widget_AbstractElement {

	protected $htmlType 		= 'text';
	protected $cssClassName 	= 'inputText';
	protected $placeholder		= '';
	protected $onChange			= null;
	protected $onKeydown		= null;
	protected $onFocus			= null;
	protected $onBlur			= null;
	protected $disabled			= false;
	protected $attributes		= array();
	protected $rows;

	public function __construct($args) {
		if (array_key_exists('placeholder', $args) && strlen($args['placeholder']))
			$this->placeholder = $args['placeholder'];
		
		if (array_key_exists('type', $args) && strlen($args['type']))
			$this->htmlType = $args['type'];
		
		if (array_key_exists('onChange', $args) && strlen($args['onChange']))
			$this->onChange = $args['onChange'];
			
		if (array_key_exists('onKeydown', $args) && strlen($args['onKeydown']))
			$this->onKeydown = $args['onKeydown'];
			
		if (array_key_exists('onFocus', $args) && strlen($args['onFocus']))
			$this->onFocus = $args['onFocus'];

		if (array_key_exists('onBlur', $args) && strlen($args['onBlur']))
			$this->onBlur = $args['onBlur'];

		if (array_key_exists('disabled', $args) && is_bool($args['disabled']))
			$this->disabled = $args['disabled'];
		
		
		if (array_key_exists('attributes', $args) && is_array($args['attributes']))
			$this->attributes = $args['attributes'];



	}

	function renderElement() {
		
		$attributesHtml = '';
		if (count($this->attributes)) 
			foreach ($this->attributes as $key => $val)
				$attributesHtml.= sf(' %s="%s" ', $key, $val);
		
		
		return(sf('<input type="%s" name="%s" value="%s" %s id="form_%s" class="%s" %s%s%s%s%s%s%s />',
			$this->htmlType,
			$this->getName(),
			parent::makeInputSafe($this->getValue()),
			($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '',
			$this->getName(),
			$this->cssClassName,
			$attributesHtml,
			strlen($this->placeholder)?sf(' placeholder="%s"', $this->placeholder):'',
			!is_null($this->onChange) && strlen($this->onChange)?sf(' onChange="%s"', $this->onChange):'',
			!is_null($this->onKeydown) && strlen($this->onKeydown)?sf(' onKeydown="%s"', $this->onKeydown):'',
			!is_null($this->onFocus) && strlen($this->onFocus)?sf(' onFocus="%s"', $this->onFocus):'',
			!is_null($this->onBlur) && strlen($this->onBlur)?sf(' onBlur="%s"', $this->onBlur):'',
			$this->disabled?' disabled':''
		));
	}

}

?>