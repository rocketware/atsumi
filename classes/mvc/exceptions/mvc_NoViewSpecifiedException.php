<?php
class mvc_NoViewSpecifiedException extends atsumi_AbstractException {

	private $method;

	public function __construct($message = null, $method, $code = 0) {
		$this->method = $method;
		parent::__construct($message, $code);
	}

	public function getInstructions($contentType) {
		switch($contentType) {
			default:
			case 'text/plain':
				return sf('--Description\nYou need to use the $this->setView() method within your function.\n
--Example Code\n
	public function %s() {

			$this->setView(string $ViewClassName);
			/* ... put your controller code here ... */

	}\n', $this->method);
				break;
			case 'text/html':

				return sf('<h4>Description</h4><p>You need to use the $this->setView() method within your function.</p>
<h4>Example Code</h4>
<pre class="code">
public function %s() {

		$this->setView(<strong>string <em>$ViewClassName</em></strong>);
		/* ... put your controller code here ... */

}
</pre>', $this->method);
				break;

		}

	}


}
?>