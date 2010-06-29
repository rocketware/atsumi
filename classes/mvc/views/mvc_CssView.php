<?php
/*
 * Created on 14 Mar 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class mvc_CssView extends mvc_AbstractView {


	public function render() {
		$this->renderCharSet();
		$this->renderCss();
	}
	
	// headers
	public function setHeaders() {
		header(sf('Content-Type: text/css; charset=%s', $this->getCharset()));
	}	
	
	protected function renderCharSet() {
		pfl('@CHARSET "%s";', strtoupper($this->getCharset()));
	}

	private function renderCss() {
		pfl('%s', $this->get_css);
	}


	
}

?>
