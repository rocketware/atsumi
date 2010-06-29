<?php

class widget_HiddenElement extends widget_AbstractElement {
	
	public function __construct($args) {
		
	}
	function renderElement() {
		return(sf("<input type='hidden' name='%s' value='%s' id='form_%s' class='text' />", 
							$this->getName(),
							parent::makeInputSafe($this->getValue()),
							$this->getName()
				));
	}
}




?>