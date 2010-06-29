<?php
/*
 * Created on 14 Mar 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class mvc_ErrorView extends mvc_AbstractView {


	public function render() {
		$this->renderDoctype();
		$this->renderHtml();
	}

	public function setHeaders() {
		header(sf('Content-Type: text/html; charset=%s', $this->getCharset()));
		// display the custom http header eg: 404
		if(!is_null($this->get_httpHeader)) header($this->get_httpHeader);
	}

	protected function getTitle() {
		return $this->get_title;
	}
	protected function renderDoctype() {
		pf('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">\n');
	}
	function renderBodyContent() {

		pfl("<div style='text-align:center; width: 600px; margin:40px auto 0px auto;'><h1>%s</h1><p>%s</p></div>",
			$this->get_title,
			$this->get_details);

	}
	function renderHeadContent() {
		pf('<title>%h</title>\n', $this->getTitle());
		$this->renderHeadCss();
		$this->renderHeadJs();
	}
	protected function renderHeadCss() {
	}
	protected function renderHeadJs() {
	}


	// private structural methods

	protected function renderHtml() {
		pf('<html>\n');
		$this->renderHead();
		$this->renderBody();
		pf('</html>\n');
	}

	protected function renderHead() {
		pf('<head>\n');
		$this->renderHeadContent();
		pf('</head>\n');
	}


	protected function renderBody() {
		pf('<body>\n');
		$this->renderBodyContent();
		pf('</body>\n');
	}


}

?>
