<?php

/**
 * mvc_HtmlView : A html version of the view
 *
 * @since 14 Mar 2008
 * @author jimmyxx <>
 */
abstract class mvc_HtmlView extends mvc_AbstractView {

	abstract protected function renderBodyContent();


	// headers
	public function setHeaders() {
		header(sf('Content-Type: text/html; charset=%s', $this->getCharset()));
	}
	public function getCharset() {
		return 'utf-8';
	}

	public function render() {
		$this->renderDoctype();
		$this->renderHtml();
	}

	protected function getTitle() {
		return 'Untitled';
	}

	protected function renderDoctype() {
		pfl('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">');
	}

	protected function renderHeadContent() {
		pfl('<title>%h</title>', $this->getTitle());
		$this->renderHeadMeta();
		$this->renderHeadCss();
		$this->renderHeadJs();
	}

	// Overidable to add content
	protected function renderHeadMeta() {}
	protected function renderHeadCss() {}
	protected function renderHeadJs() {}

	// Private structural methods
	protected function renderHtml() {
		pfl('<html>');
		$this->renderHead();
		$this->renderBody();
		pfl('</html>');
	}

	protected function renderHead() {
		pfl('<head>');
		$this->renderHeadContent();
		pfl('</head>');
	}

	protected function renderBody() {
		pfl('<body>');
		$this->renderBodyContent();
		pfl('</body>');
	}
}
?>