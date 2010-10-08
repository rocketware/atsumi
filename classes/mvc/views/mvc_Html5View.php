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

	protected function getDescription() {
		return '';
	}

	protected function getAuthor() {
		return '';
	}

	protected function getShortcutIcon() {
		return '';
	}

	protected function getAppleIcon() {
		return '';
	}

	protected function getJQuery() {
		return '';
	}

	protected function getGoogleAnalytics() {
		return '';
	}

	protected function renderDoctype() {
		pfl('<!doctype html>');
	}

	protected function renderHeadContent() {
		pfl('<meta charset="%s">', $this->getCharset());
		pfl('<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">');
		pfl('<title>%h</title>', $this->getTitle());

		if($this->getDescription() !== '')
			pfl('<meta name="description" content="%s">', $this->getDescription());

		if($this->getAuthor() !== '')
			pfl('<meta name="author" content="%s">', $this->getAuthor());

		$this->renderHeadMeta();
		$this->renderHeadLink();
		$this->renderHeadJs();
	}

	// Overidable to add content
	protected function renderHeadMeta() {
		//pfl('<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">');
	}
	protected function renderHeadLink() {
		//pfl('<link rel="stylesheet" href="css/style.css?v=1">');
		//pfl('<link rel="stylesheet" media="handheld" href="css/handheld.css?v=1">');
	}
	protected function renderHeadJs() {
		//pfl('<script src="js/modernizr-1.5.min.js"></script>');
	}
	protected function renderHeadJs() {

	}

	// Private structural methods
	protected function renderHtml() {
		pfl('<html>');
		$this->renderHead();
		$this->renderBody();
		pfl('</html>');
	}

	protected function renderHead() {
		pfl('<head lang="en" class="no-js">');
		$this->renderHeadContent();
		pfl('</head>');
	}

	protected function renderBody() {
		pfl('<body>');
		$this->renderBodyContent();
		$this->renderBodyJs();

		if($this->getJQuery() !== '') {
			pfl('<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>');
			pfl(
				'<script>!window.jQuery && document.write(unescape(\'%3Cscript src="%s"%3E%3C/script%3E\'))</script>',
				$this->getJQuery()
			);
		}


		pfl('</body>');
	}
}
?>