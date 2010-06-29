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
 * Exception thrown when the developers version of PHP is not compatable with the Atsumi version
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_PhpVersionException extends Exception {

	/**
	 * Creates a new atsumi_PhpVersionException instance
	 * @access public
	 * @param string $requiredVersion The version required by atsumi
	 */
	public function __construct($requiredVersion) {
		parent::__construct('Atsumi requires a PHP version of '.$requiredVersion.' or higher');
	}
}
?>