<?php

/**
 * Base abstract session storage handler
 */
class session_AbstractStorage {

	/**
	 * Constructor
	 *
	 * @param array $options optional parameters
	 */
	public function __construct($options = array()) {
		$this->register();
	}

	/**
	 * Returns a reference to a session storage storage object
	 * Only create one if it doesn't exist
	 *
	 * @param name $name The session store to instantiate
	 * @return sessionStorage A Atsumi SessionStorage object
	 */
	public function getInstance($options = array(), $storage = session_Handler::DEFAULT_STORAGE) {
		static $instance;

		if(!is_object($instance)) {
			if(!is_subclass_of($storage, 'session_AbstractStorage'))
				throw new Exception('Must be a valid session storage object');

			$instance = new $storage($options);
		}

		return $instance;
	}

	/**
	 * Set the user-level session storage functions
	 *
	 * @param array $options Optional parameters
	 */
	public function register() {
		// Use this object as the session handler
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * Executed when the session is being opened
	 *
	 * @param string $savePath The path to the session object.
	 * @param string $sessionName The name of the session.
	 * @return boolean Returns TRUE on success or FALSE on failure
	 */
	public function open($savePath, $sessionName) {
		return true;
	}

	/**
	 * Executed when the session operation is being closed
	 *
	 * @return boolean Returns TRUE on success or FALSE on failure
	 */
	public function close() {
		return true;
	}

 	/**
 	 * Read the data for a particular session identifier
 	 *
 	 * @param string $id The session identifier
 	 * @return string The session data
 	 */
	public function read($id) {
		return;
	}

	/**
	 * Write session data to the SessionHandler backend
	 *
	 * @param string $id The session identifier
	 * @param string $sessionData The session data
	 * @return boolean Returns TRUE on success or FALSE on failure
	 */
	public function write($id, $sessionData) {
		return true;
	}

	/**
	  * Destroy the data for a particular session
	  *
	  * @param string $id The session identifier
	  * @return boolean Returns TRUE on success or FALSE on failure
	  */
	public function destroy($id) {
		return true;
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend
	 *
	 * @param integer $maxlifetime  The maximum age of a session
	 * @return boolean Returns TRUE on success or FALSE on failure
	 */
	public function gc($maxlifetime) {
		return true;
	}

	/**
	 * Test to see if the SessionHandler is available
	 *
	 * @return boolean Returns TRUE on success or FALSE on failure
	 */
	public function test() {
		return true;
	}
}
?>