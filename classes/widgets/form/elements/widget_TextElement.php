<?php

class widget_TextElement extends widget_AbstractElement {

	protected $htmlType 		= 'text';
	protected $cssClassName 	= 'inputText';
	protected $placeholder		= '';
	protected $rows;

	public function __construct($args) {
		if (array_key_exists('placeholder', $args) && strlen($args['placeholder']))
			$this->placeholder = $args['placeholder'];
	}

	function renderElement() {
		return(sf('<input type="%s" name="%s" value="%s" id="form_%s" class="%s"%s />',
							$this->htmlType,
							$this->getName(),
							parent::makeInputSafe($this->getValue()),
							$this->getName(),
							$this->cssClassName,
							strlen($this->placeholder)?sf(' placeholder="%s"', $this->placeholder):''
				));
	}
}

?>