<?php

class widget_HtmlRowElement extends widget_AbstractElement {
	
	protected $htmlType = 'htmlRow';
	
	public function __construct($args) {

	}

	function renderElement() {
		return(sf("<div class='htmlRow'>%s</div>", 
							$this->getValue()
				));
	}	
}

?>