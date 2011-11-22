<?php
class mvc_JsonpView extends mvc_AbstractView {

	public function render() {
		pfl('%s(%s)', is_null($this->get_callback)?'callback':$this->get_callback, $this->get_json);
	}

	public function setHeaders() {
		header(sf('Content-Type: application/json; charset=%s', $this->getCharset()));
	}
}
?>