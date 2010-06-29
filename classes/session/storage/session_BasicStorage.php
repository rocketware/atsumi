<?php

/**
 * Basic session storage handler, this just allows php to control the session
 */
class session_BasicStorage extends session_AbstractStorage {

	/**
	 * Set the user-level session storage functions
	 *
	 * @param array $options Optional parameters
	 */
	public function register() {
		// Let php handle the session storage
	}
}
?>