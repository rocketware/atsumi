<?php
class mvc_JsonView extends mvc_AbstractView {

	public function render() {
		pfl('%s', $this->get_json);
	}

	public function setHeaders() {
		header(sf('Content-Type: application/json; charset=%s', $this->getCharset()));
	}
}
?>