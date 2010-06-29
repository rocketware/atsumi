<?php

class widget_TextElementWithSurroundingText extends widget_TextElement {
	
	protected $htmlType = 'text';
	
	public function __construct($args) {
		$this->beforeText 	= $args['beforeText'];
		$this->afterText 	= $args['afterText'];
		$this->inputStyle	= $args['style'];
	}

	function renderElement() {
		
		$ret = "";
		
		if( $this->beforeText != '' )
			$ret .= sf('<span style="color:#777; font-size:0.7em;">%s</span> ', $this->beforeText);
		
		$ret .= sf("<input type='%s' name='%s' value='%s' id='form_%s' class='text' style='%s' />", 
							$this->htmlType,
							$this->getName(),
							parent::makeInputSafe($this->getValue()),
							$this->getName(),
							$this->inputStyle
				);

		if( $this->afterText != '' )
			$ret .= sf(' <span style="color:#777; font-size:0.7em;">%s</span>', $this->afterText);
		
		return $ret;
			
	}
}


?>