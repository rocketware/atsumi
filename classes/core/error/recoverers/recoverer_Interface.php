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
 * Interface that must be implemented by all error recoverers
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
interface recoverer_Interface {
	/* GET METHODS */

	/**
	 * Returns a description of how the class is handling the error
	 * @access public
	 * @return string A description of how the class is handling the error
	 */
	public function getActionDetails();

	/* SET METHODS */
	/* METHODS */

	/**
	 * Preforms the required actions to recover from an exception
	 * @access public
	 * @param Exception $e The exception to recover from
	 */
   	public function recover($e);
}
?>