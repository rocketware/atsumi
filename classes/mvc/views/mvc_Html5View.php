<?php

/**
 * Represents a valid Html5 page template that can be extended from to create more compleax pages.
 * @since Friday 8th, October 2010
 */
abstract class mvc_Html5View extends mvc_AbstractView {

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
		pf('<meta charset="%s">', $this->getCharset());
		pf('<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">');
		pf('<title>%h</title>', $this->getTitle());

		if($this->getDescription() !== '')
			pf('<meta name="description" content="%s">', $this->getDescription());

		if($this->getAuthor() !== '')
			pf('<meta name="author" content="%s">', $this->getAuthor());

		$this->renderHeadMeta();

		if($this->getShortcutIcon() !== '')
			pf('<link rel="shortcut icon" href="%s">', $this->getShortcutIcon());

		if($this->getAppleIcon() !== '')
			pf('<link rel="apple-touch-icon" href="%s">', $this->getAppleIcon());

		$this->renderHeadLink();
		$this->renderHeadJs();
	}

	/**
	 * Render any Meta data
	 */
	protected function renderHeadMeta() {
		//pf('<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">');
	}

	/**
	 * Render any document links such as Stylesheets
	 */
	protected function renderHeadLink() {
		//pf('<link rel="stylesheet" href="css/style.css?v=1">');
		//pf('<link rel="stylesheet" media="handheld" href="css/handheld.css?v=1">');
	}

	/**
	 * Renders any header JavaScript
	 *
	 * NOTE: It is recommended that you load all your JavaScript at the bottom of the page using the
	 * renderBodyJs file for faster loading. One exception is Modernizr, although not required will
	 * enable HTML5 elements & feature detection in non-complient browsers.
	 */
	protected function renderHeadJs() {
		//pf('<script src="js/modernizr-1.5.min.js"></script>');
	}

	/**
	 * Renders any Body JavaScript
	 *
	 * NOTE: It is recommended you load your JavaScript here for faster page loading.
	 */
	protected function renderBodyJs() {

	}

	// Private structural methods
	protected function renderHtml() {
		pf('<html lang="en" class="no-js">');
		$this->renderHead();
		$this->renderBody();
		pf('</html>');
	}

	protected function renderHead() {
		pf('<head>');
		$this->renderHeadContent();
		pf('</head>');
	}

	/**
	 * Renders the page body, optionally rendering JQuery include code.
	 */
	protected function renderBody() {
		pf('<body>');
		$this->renderBodyContent();

		// If a JQuery file is provided render the JQuery include code.
		if($this->getJQuery() !== '') {
			pfl('<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>');
			pfl(
				'<script>!window.jQuery && document.write(unescape(\'%%3Cscript src="%s"%%3E%%3C/script%%3E\'))</script>',
				$this->getJQuery()
			);
		}

		$this->renderBodyJs();
		pf('</body>');
	}
}
?>