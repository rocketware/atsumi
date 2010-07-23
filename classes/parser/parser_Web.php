<?php

/* generic parser for an web project */
class parser_Web extends parser_Abstract {
	
	protected $spec = array (
		's' => 'string',
		'd' => 'd',
		'h' => 'html',
		'H' => 'htmlLineBreaks',
		'j' => 'javascriptEscape',
		'u' => 'urlEncode',
		'U' => 'urlDecode'
		);
		
	/* annoyance due to PHP scope issue */
	static function parse ($args) {
		$parser =  new self();
		return  (string) $parser->parseString(func_get_args());
	}
		
	static function string ($in) {
		return $in;	
	}
	static function d ($in) {
		return sprintf('%d', $in);
	}
	static function html ($in) {
		return htmlspecialchars($in, ENT_COMPAT, 'UTF-8');
	}
	static function htmlLineBreaks ($in) {
		return nl2br(htmlspecialchars($in, ENT_COMPAT, 'UTF-8'));
	}
	static function javascriptEscape ($in) {
		return str_replace (
			array('\\', '\'', '"', '/', "\n", "\r"),
			array('\\\\', '\\\'', '\\"', '\\/', '\\n', '\\r'),
			$str
		);
	}
	static function urlEncode ($in) {
		return urlencode($in);
	}
	static function urlDecode ($in) {
		return urldecode($in);
	}
}

?>