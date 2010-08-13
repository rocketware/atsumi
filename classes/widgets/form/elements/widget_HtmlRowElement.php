<?php

class widget_HtmlRowElement extends widget_AbstractElement {

	protected $htmlType = 'htmlRow';
	protected $html;

	public function __construct ($args) {


		$this->html = array_key_exists('html',$args)?$args['html']:null;
	}

	function renderElement () {
		return (sf("<div class='htmlRow'>%s</div>",
							is_null($this->html)?$this->getValue():$this->html
				));
	}

	public function setValue ($input, $files = array()) {
		$this->value = isset($input[$this->name]) ? $input[$this->name] : null;
	}
}

?>