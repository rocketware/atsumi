<?php

/**
 *
 * @package Database
 * @subpackage Encryption
 */
class encrypt_Simple implements encrypt_Interface {

	protected $seed;

	public function __construct($seed = 'this-is-a-seed') {
		$this->seed = $seed;
	}

	public function hash($string) {
		return hash('sha1', $this->seed.$string);
	}

	public function encrypt($string) {

	}

	public function decrypt($string) {

	}
}
?>