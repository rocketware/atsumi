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
			$ret .= sf('<span class="inputTextSurroundPre">%s</span> ', $this->beforeText);

		$ret .= sf("<input type='%s' name='%s' value='%s' %s id='form_%s' class='inputTextSurround' style='%s' />",
							$this->htmlType,
							$this->getName(),
							parent::makeInputSafe($this->getValue()),
							($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '',
							$this->getName(),
							$this->inputStyle
				);

		if( $this->afterText != '' )
			$ret .= sf(' <span class="inputTextSurroundPost">%s</span>', $this->afterText);

		return $ret;

	}
}


?>