<?php

/**
 *
 * @package Database
 * @subpackage Encryption
 */
interface encrypt_Interface {

	/**
	 * One way hash function
	 *
	 * @param $string string The string to be hashed
	 * @return string The hashed string
	 */
	public function hash($string);

	/**
	 * Encripts a string
	 *
	 * @param $string string The string to be encripted
	 * @return string The hashed string
	 */
	public function encrypt($string);

	/**
	 * Decripts a string
	 *
	 * @param $string string The string to be decripted
	 * @return string The hashed string
	 */
	public function decrypt($string);
}
?>