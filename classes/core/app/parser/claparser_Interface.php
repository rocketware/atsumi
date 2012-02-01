<?php
/**
 * A base interface class for cla parsers
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
interface claparser_Interface {
	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

   	/**
   	 * Parses a command using the given specification to determine the required controller, method and args
   	 * @access public
   	 * @param string $uri The uri to parse
   	 * @param array $specfication The specification to parse against
   	 */
	public function parseCommand($path, $specfication);
}
?>
