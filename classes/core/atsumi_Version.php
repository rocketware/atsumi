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
 * Class defining the Atsumi version data
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_Version {
	/* CONSTANTS */

	/**
	 * Defines the PHP version Atsumi requires to work correctly
	 * @var string
	 */
	const REQUIRED_PHP_VERSION = '5.2.0';

	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Test weather or not Atsumi is compatable with the current PHP version
	 * @access public
	 * @return boolean If Atsumi is compatable with the current PHP version
	 */
	public static function PhpCompatable() {
		return (defined('PHP_VERSION') ? (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION) >= 0) : false);
	}
}
?>