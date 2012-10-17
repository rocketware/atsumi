<?php
/**
 * @version		0.90
 * @package		Atsumi.Framework
 * @copyright	Copyright (C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Used to convert exception objects into renderable content
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_ErrorParser {
	/* CONSTANTS */

	/**
	 * Defines the output to be of type XHTML
	 * @var string
	 */
	const HTML = 'text/html';

	/**
	 * Defines the output to be of type Plain Text
	 * @var string
	 */
	const PLAINTEXT = 'text/plain';

	/**
	 * Defines the output to be of type Json
	 * @var string
	 */
	const JSON = 'application/json';

	/**
	 * Defines the output to be of type Xml
	 * @var string
	 */
	const XML = 'text/xml';

	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Parses an exception into a content type
	 * @access public
	 * @param Exception $e The exception to parse
	 * @param string $contentType The type of content to return
	 * @param string $recoverer The recoverer used to recover from the exception
	 * @return string A parsed verion of the exception as the supplied content type
	 */
	public static function parse(Exception $e, $contentType = self::HTML, $recoverer = null) {
		$out = '';
		switch($contentType) {
			default:
			case self::PLAINTEXT:
				$out .= sfl('###### ATSUMI has caught an Exception : %s', date(DATE_ATOM));
				$out .= sfl("\n".' >> %s <<', $e->getMessage());
				$out .= sfl("\n".'Exception type: '.get_class($e));
				if($e instanceof ErrorException)
					$out .= sfl("\n".'Severity level: '.$e->getSeverity());

				$out .= sfl("\n".'%s #%s', $e->getFile(), $e->getLine());

				/* Show the request URL if avalable */
	    		if (isset($_SERVER) && is_array($_SERVER) && array_key_exists('HTTP_HOST', $_SERVER) && array_key_exists('REQUEST_URI', $_SERVER))
	    			$out .= sfl("\nRequest: http://%s", $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	
				/* Show the referer URL if avalable */
	    		if (isset($_SERVER) && is_array($_SERVER) && array_key_exists('HTTP_REFERER', $_SERVER))
	    			$out .= sfl("\nReferer: %s", $_SERVER['HTTP_REFERER']);	    			
	    		
	    			
				if(!is_null($recoverer)) {
					$out .= sfl("\n".'Recoverer: %s()', (is_object($recoverer) ? get_class($recoverer) : $recoverer));
	 				$out .= sfl('Recoverer action: %s', $recoverer->getActionDetails());
				}

				if (isset($e->details) && !is_null($e->details)) {
					$out .= sfl("\n".'-Additional Detail');
					$out .= sfl('%s', pretty($e->details));
				}


				if($e instanceof atsumi_AbstractException) {
					$out .= sfl("\n".'-How to resolve this issue');
					$out .= sfl($e->getInstructions('text/plain'));
				}


				$out .= sfl("\n".'-Stack Trace');
				$out .= sfl('%s', atsumi_ErrorParser::formatTrace($e->getTrace()));
				$out .= sfl('###### End of Exception '."\n\n");
				break;
			case 'text/html':
				$out .= sfl(self::getHtmlCss());
				$out .= sfl('<div class="atsumiError">');
				$out .= sfl('<h3><strong>ATSUMI</strong> has caught an Exception : <strong>%s</strong></h3>', date(DATE_ATOM));
				$out .= sfl('<h1>%s</h1>', $e->getMessage());
				$out .= sfl('<h4>Exception type: <strong>%s</strong></h4>', get_class($e));
				if($e instanceof ErrorException)
					$out .= sfl('<h4>Severity level: <strong>%s</strong></h4>', $e->getSeverity());

				$out .= sfl('<h2>%s #<strong>%s</strong></h2>', preg_replace('|\/([a-zA-Z0-9\-\_\.]+\.php)|',  '/<strong>\\1</strong>', htmlentities($e->getFile())), $e->getLine());

				/* Show the request URL if avalable */
	    		if (isset($_SERVER) && is_array($_SERVER) && array_key_exists('HTTP_HOST', $_SERVER) && array_key_exists('REQUEST_URI', $_SERVER))
	    			$out .= sfl("<h4>Request: <strong><a href='http://%s'>http://%s</a></strong></h4>", $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	    			
				/* Show the referer URL if avalable */
	    		if (isset($_SERVER) && is_array($_SERVER) && array_key_exists('HTTP_REFERER', $_SERVER))
	    			$out .= sfl("<h4>Referer: <strong><a href='%s'>%s</a></strong></h4>", $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_REFERER']);
	    			
				if(!is_null($recoverer)) {
					$out .= sfl('<h4>Recoverer: <strong>%s()</strong></h4>', (is_object($recoverer) ? get_class($recoverer) : $recoverer));
	 				$out .= sfl('<h4>Recoverer action: <strong>%s</strong></h4>', $recoverer->getActionDetails());
				}

				if (isset($e->details) && !is_null($e->details)) {
					$out .= sfl('<br /><h3>Additional Detail</h3>');
					$out .= sfl('<div class="atsumiDetailsContainer"><pre>%s</pre></div>', pretty($e->details));
				}

				if($e instanceof atsumi_AbstractException) {
					$out .= sfl('<br /><h3><strong>ATSUMI</strong>: How to resolve this issue</h3>');
					$out .= sfl('<div class="atsumiDetailsContainer">%s</div>', $e->getInstructions('text/html'));
				}

				$out .= sfl('<br /><h4>Stack Trace</h4>');
				$out .= sfl('<div class="atsumiDetailsContainer"><pre>%s</pre></div>', atsumi_ErrorParser::formatTrace($e->getTrace(), 'text/html'));
				$out .= sfl('</div>');
				break;
		}
		return $out;
	}

	/**
	 * Returns all CSS used by the error parser when displaying html exceptions
	 * @access public
	 * @return string The required CSS
	 */
	public static function getHtmlCss() {
		return sfl('
			<style type="text/css">
			div.atsumiError { border:1px solid #8c897e; min-width:500px; margin:20px; padding:20px; background-color:#e1ded4;  font-family:arial, sans, verdana; line-height:1.5em; -moz-border-radius:0.5em; -webkit-border-radius:0.5em; }
			div.atsumiError * { }
			div.atsumiError h1 { font-size:26px; color:#222;  }
			div.atsumiError h2 { font-size:22px; color:#8f8a7f;  }
			div.atsumiError h3 { font-size:17px; margin:0px; color:#8f8a7f; }
			div.atsumiError h4 { color:#8f8a7f; font-size:14px; margin:0px; }
			div.atsumiError .atsumiObject,div.atsumiError .atsumiClass { color:#724a23; }
			div.atsumiError .atsumiMethod { color:#658823; }
			div.atsumiError .atsumiFile { color:#444; }
			div.atsumiError strong {  color:#724a23;  }
			div.atsumiError .atsumiDetailsContainer { overflow-x: auto; margin:5px 0px 0px 0px; padding:5px 10px 5px 10px; background-color:#efeeea; border:1px solid #f4f4f4; -moz-border-radius:0.5em; -webkit-border-radius:0.5em; }
			div.atsumiError pre.code { background-color:#222; color:#fff; padding:15px 15px 15px 50px; border:1px solid #7db722; margin:10px 10px 15px 10px;  }
			div.atsumiError pre.code strong { color:#d3ff3b; }
			</style>'
		);
	}

	/**
	 * Formats a backtrace into a content type
	 * @access protected
	 * @param array $trace The trace to format
	 * @param string $contentType The type of content to return
	 */
	protected static function formatTrace($trace, $contentType = 'text/plain') {
		$out = '';
		$row = 0;
		foreach($trace as $i => $l) {
			if(array_key_exists('class', $l) && $l['class'] == 'atsumi_ErrorHandler')
				continue;

			$args = (array_key_exists('args', $l) ? self::argsToString($l['args']) : '');

			switch($contentType) {
				default:
				case 'text/plain':
					$location = array_key_exists('file', $l)?
						sf("%s(%s): ", str_replace(atsumi_Loader::getWorkspace(), 'WORKSPACE', $l['file']), $l['line'])
						: "[internal function]: ";

					$out .= sfl('#%s %s%s%s',
						$row,
						$location,
						array_key_exists('class', $l) ?
							array_key_exists('object', $l) ?
								sf('%s->%s', $l['class'], $l['function'])
									: sf('%s::%s', $l['class'], $l['function'])
							: $l['function'],
						sf('(%s)', $args)
					);
					break;
				case 'text/html':
					$location = array_key_exists('file', $l)?
						sf("%s(<strong>%s</strong>): ",
							preg_replace(
								'|\/([a-zA-Z0-9\-\_\.]+\.php)|',
								'/<strong class="atsumiFile">\\1</strong>',
								htmlentities(str_replace(atsumi_Loader::getWorkspace(), 'WORKSPACE', $l['file']))
							),
							$l['line']
						)
						: "[internal function]: ";

					$out .= sfl("#<strong>%s</strong> %s%s%s",
						$row,
						$location,
						array_key_exists('class', $l)?
							array_key_exists('object', $l)?
									sf("<strong class='atsumiObject'>%s</strong>-><strong class='atsumiMethod'>%s</strong>", $l['class'], $l['function'])
									: sf("<strong class='atsumiClass'>%s</strong>::<strong class='atsumiMethod'>%s</strong>", $l['class'], $l['function'])
							: sf("%s", $l['function']),
						sf("(%s)", $args)
					);

					break;
			}
			$row++;
		}
		return $out;
	}

	/**
	 * Converts a variable into a nice format
	 * @access protected
	 * @param $in The arg to convert
	 */
	protected static function argsToString($in) {
		if(is_string($in)) return $in;
		$text = array();
		foreach($in as $key => $item) {
			switch(gettype($item)) {
				case 'boolean':
					$string = ($item ? 'true' : 'false');
					break;
				case 'integer':
					$string = strval($item);
					break;
				case 'double':
					$string = strval($item);
					break;
				case 'array':
					$string = sf('Array(%s)', strval(self::argsToString($item)));
					break;
				case 'object':
					$string = sf('%s()',get_class($item));
					break;
				case 'string':
					$string = sf('\'%s\'',$item);
					break;
				case 'resource':
					$string = sf('Resource #%s', get_resource_type($item));
					break;
				case 'null':
					$string = 'NULL';
					break;
				case 'unknown type':
				default:
					try {
						$string = strval($item);
					} catch(Exception $e) {
						$string = '???';
					}
					break;
			}
			$text[] = $string;
		}
		return implode(', ', $text);
	}
}
?>