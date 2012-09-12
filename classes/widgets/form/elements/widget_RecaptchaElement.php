<?php
class widget_RecaptchaElement extends widget_AbstractElement {
	private $options = array();

	private $publicKey = '';

	public function __construct($args) {
		if(!isset($args['apiKey'])) {
			throw new Exception('Recaptcha widget required the apiKey index to be set');
		}
		$this->publicKey = $args['apiKey'];
	}
	function getRequired () {
		return true;
	}
	function renderElement() {
		$output = sf('
			<script type="text/javascript"src="http://www.google.com/recaptcha/api/challenge?k=%s"></script>
			<noscript>
				<iframe src="http://www.google.com/recaptcha/api/noscript?k=%s" height="300" width="500" frameborder="0"></iframe><br>
					<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
					<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
			</noscript>', $this->publicKey, $this->publicKey);
		return $output;
	}
}
?>
