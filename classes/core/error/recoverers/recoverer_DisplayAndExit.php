<?php
/**
 * @version		0.90
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Recovers from an error by displaying it and exiting
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class recoverer_DisplayAndExit implements recoverer_Interface {
	/* CONSTANTS */
	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */

	/**
	 * Tries to work out what the content type of the page is based on currently set headers
	 * @access public
	 * @return string The content type of the page, or 'text/html' as default
	 */
	public static function getContentType() {
		$headers = headers_list();
		foreach($headers as $header)
			if(preg_match("/^content-type: (.*)$/i", $header, $match))
				return strtolower($match[1]);

		return 'text/html';
	}

	/**
	 * Returns a description of how the class is handling the error
	 * @access public
	 * @return string A description of how the class is handling the error
	 */
	public function getActionDetails() {
		return 'Display debug data and exit.';
	}

	/* SET METHODS */

	/**
	 * Sets any error headers that need to be set to inform the browser it is displaying an error
	 * @access protected
	 */
	protected function setHeaders() {
		// if(!headers_sent()) header('HTTP/1.0 500 Internal server error');
	}

	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Preforms the required actions to recover from an exception
	 * @access public
	 * @param Exception $e The exception to recover from
	 */
	public function recover($e) {
		$this->setHeaders();
		pf('%s', atsumi_ErrorParser::parse($e, self::getContentType(), $this));
		exit;
	}
}
?>