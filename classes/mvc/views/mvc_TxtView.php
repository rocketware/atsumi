<?php
/*
 * Created on 14 Mar 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class mvc_TxtView extends mvc_AbstractView {


	public function render() {
		pfl('%s', $this->get_txt);
	}
	
	// headers
	public function setHeaders() {
		header(sf('Content-Type: text/plain; charset=%s', $this->getCharset()));
	}	
	


	
}

?>
