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
 * The atsumi abstract exception which can be inherited form inorder to provide the developer
 * with more detailed information about the exception and why it has been thrown
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
abstract class atsumi_AbstractException extends Exception {
	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */
	/* METHODS */

	/**
	 * Returns further information on how to solve the the exception
	 * @access public
	 * @param string $contentType The context type(html|text)
	 * @return string Forther information of the content type passed to the function
	 */
	abstract public function getInstructions($contentType);
}
?>