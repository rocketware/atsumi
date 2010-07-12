<?php

class widget_TextElement extends widget_AbstractElement {

	protected $htmlType 		= 'text';
	protected $cssClassName 	= 'inputText';
	protected $rows;

	public function __construct($args) {

	}

	function renderElement() {
		return(sf('<input type="%s" name="%s" value="%s" id="form_%s" class="%s" />',
							$this->htmlType,
							$this->getName(),
							parent::makeInputSafe($this->getValue()),
							$this->getName(),
							$this->cssClassName
				));
	}
}

?>