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
 * Represents arguments that were stored when an error event occured
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_ErrorEventArgs extends atsumi_EventArgs {

	/**
	 * The exception that was thrown
	 * @access public
	 * @var Exception
	 */
	public $exception;

	/**
	 * The recover that was used
	 * @access public
	 * @var string
	 */
	public $recoverer;

	/**
	 * Creates a new atsumi_ErrorEventArgs instance
	 * @param Exception $e The exception that was thrown
	 * @param string $recover The recover that was used
	 */
	public function __construct(Exception $e, $recoverer = null) {
		$this->exception 	= $e;
		$this->recoverer 	= $recoverer;
	}
}
?>