<?php

/**
 * XmlView
 *
 * @author James Oates <james.oates@apnuk.com>
 */
class mvc_XmlView extends mvc_AbstractView {

	// Render the page
	public function render() {
		pfl('%s', $this->get_txt);
	}

	// Set Headers
	public function setHeaders() {
		header(sf('Content-Type: text/xml; charset=%s', $this->getCharset()));
	}
}
?>
