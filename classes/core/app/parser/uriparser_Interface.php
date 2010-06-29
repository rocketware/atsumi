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
 * A base interface class for uri parsers
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
interface uriparser_Interface {
	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

   	/**
   	 * Creates a uri for a given controller and method
   	 * @static
   	 * @access public
   	 * @param array $specification The specification to use to create
   	 * @param string $controller The name of the controller to be used
   	 * @param string $method The name of the method to be used
   	 * @param array $args A one dimentional array of args to pass to the method
   	 */
	public function createUri($specification, $controller, $method, $args = array());

   	/**
   	 * Parsers a uri using the given specification to determine the required controller, method and args
   	 * @access public
   	 * @param string $uri The uri to parse
   	 * @param array $specfication The specification to parse against
   	 */
	public function parseUri($path, $specfication);
}
?>