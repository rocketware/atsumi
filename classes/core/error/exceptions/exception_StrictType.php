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
 * Thrown when a variable type could not be strictly cast
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class exception_StrictType extends Exception {
	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new exception_StrictType instance
	 * @access public
	 * @param string $expected The type expected
	 * @param mixed $input The variable received
	 */
	public function __construct($expected, $input) {
		parent::__construct(sf(
			'Strict %s cast failed: %s of type %s supplied.',
			$expected,
			$input,
			gettype($input)
		));
	}

	/* METHODS */
}
?>