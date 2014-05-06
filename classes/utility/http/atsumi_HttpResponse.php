<?php
class atsumi_HttpResponse {

	private $httpCode;
	private $header;
	private $body;

	function __construct($httpCode, $header, $body) {

		$this->httpCode = $httpCode;
		$this->header = $header;
		$this->body = $body;

	}

	public function getHttpCode() { return $this->httpCode; }
	public function getBody() { return $this->body; }
	public function getHeader() { return $this->header; }
	public function getSuccessResponse() {
		return $this->httpCode >= 200 && $this->httpCode < 300;
	}

	public function getSummaryString () {

		return sf('HTTP CODE: %s, SUCCESS: %s, BODY:%s',
			$this->httpCode,
			$this->getSuccessResponse()?'YES':'NO',
			empty($this->body)?'EMPTY':sf('"%s"', $this->body)
		);

	}


}
?>