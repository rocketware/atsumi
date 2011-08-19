<?php
/*
 * Created on 14 Mar 2008
 * 
 */

class mvc_TxtView extends mvc_AbstractView {

	public function render() {
		pfl('%s', $this->get_txt);
	}
	
	// headers
	public function setHeaders() {
		header(sf('Content-Type: text/plain; charset=%s', $this->getCharset()));
		header(sf('Content-Length: %s', strlen($this->get_txt)));
	}	
		
}

?>
