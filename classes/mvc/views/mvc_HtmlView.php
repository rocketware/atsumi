<?php

/**
 * mvc_HtmlView : A html version of the view
 *
 * @since 14 Mar 2008
 * @author jimmyxx <>
 */
abstract class mvc_HtmlView extends mvc_AbstractView {

	abstract protected function renderBodyContent();

	public function render() {
		$this->renderDoctype();
		$this->renderHtml();
	}

	protected function getTitle() {
		return 'Untitled';
	}

	protected function renderDoctype() {
		pf('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">\n');
	}

	protected function renderHeadContent() {
		pf('<title>%h</title>\n', $this->getTitle());
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