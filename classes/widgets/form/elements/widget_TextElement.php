<?php

class widget_TextElement extends widget_AbstractElement {

	protected $htmlType = 'text';
	protected $rows;

	public function __construct($args) {

	}

	function renderElement() {
		return(sf('<input type="%s" name="%s" value="%s" id="form_%s" class="text" />',
							$this->htmlType,
							$this->getName(),
							parent::makeInputSafe($this->getValue()),
							$this->getName()
				));
	}
}

?>