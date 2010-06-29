<?php

class widget_HtmlElement extends widget_AbstractElement {
	
	private $html = '';
	
	public function __construct($args) {

		if(array_key_exists('html', $args))
			$this->html = $args['html'];
	}

	function renderElement() {
	}	
	public function render() {
		$out = $this->preRender();
		$out .= sf("%s", $this->html);
		$out .= $this->postRender();
		return $out;
		
	}
}




?>