<?php

/*
 * The main session manager
 */
class session_Handler {

	/* Defaults */
	const DEFAULT_STORAGE		= 'session_BasicStorage';
	const DEFAULT_NAMESPACE		= 'default';

	/* Common Namespaces */
	const SESSION_NAMESPACE		= 'session';

	/* Possible States */
	const STATE_ACTIVE			= 1; // The session is active and alive
	const STATE_EXPIRED			= 2; // The session has passed its expiry time
	const STATE_RESTARTING		= 3; // The session is being reset
	const STATE_DESTROYED		= 4; // The session has been killed
	const STATE_ERROR			= 5; // The session is in error!

	/**
	 * The current state of the session
	 * @var integer
	 */
	public $state			= self::STATE_ACTIVE;

	/**
	 * The session's storage object
	 * @var object
	 */
	public $storage			= null;

	/**
	 * Constructor - Must use getInstance to get a session singalton object
	 */
	protected function __construct($options = array()) {
		$debugger = Atsumi::debugger();

		// Start timer for constructor compleate time
		$debugger->startTimer();

		$this->configure($options);

		// Fetch the session storage handler
		$storage =(isset($options['storage']) ? $options['storage'] : self::DEFAULT_STORAGE);

		if(is_subclass_of($storage, 'session_AbstractStorage'))
			$this->storage = new $storage($options);

		// Start the session
		$this->start();

		// Add debug information
		Atsumi::debug__log(
			'Session Created',
			sf('The atsumi session constructer compleated in %s', $debugger->endTimer()),
			atsumi_Debug::AREA_SESSION
		);
	}

	/**
	 * Destructor - Will end and write the session if destroyed
	 */
	public function __destruct() {
		$this->close();
	}

	public function getId() {
		return session_id();
	}

	/**
	 * Returns a singleton instance of atsumi session
	 *
	 * @param $options Session setup options
	 * @return object A session_Handler object
	 */
	static public function getInstance($options = array()) {
		static $instance;

		if(!is_object($instance))
			$instance = new self($options);

		return $instance;
	}

	/* GET FUNCTIONS */

	public function &get($name, $default = null, $namespace = self::DEFAULT_NAMESPACE) {
		// Add prefix to prevent namespace collisions
		$namespace = '__'.$namespace;

		if(isset($_SESSION[$namespace][$name]))
			return $_SESSION[$namespace][$name];

		return $default;
	}

	/* SET FUNCTIONS */

	public function set($name, $value, $namespace = self::DEFAULT_NAMESPACE) {
		// Add prefix to prevent namespace collisions
		$namespace = '__'.$namespace;

		$old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;

		if($value === null)
			unset($_SESSION[$namespace][$name]);
		else
			$_SESSION[$namespace][$name] = $value;

		return $old;
	}

	/* HAS FUNCTIONS */

	public function has($name, $namespace = self::DEFAULT_NAMESPACE) {
		// Add prefix to prevent namespace collisions
		$namespace = '__'.$namespace;

		return isset($_SESSION[$namespace][$name]);
	}

	public function del($name, $namespace = self::DEFAULT_NAMESPACE) {
		$this->set($name, null, $namespace);
	}

	protected function start() {
		if(headers_sent() === true)
			throw new session_HeadersSentException;

		if($this->state === self::STATE_RESTARTING)
			$this->generateId();

		session_cache_limiter('none');
		session_start();

		$this->state = self::STATE_ACTIVE;
	}

	public function destroy() {
		session_destroy();
		$this->destroyCookie();

		$this->state = self::STATE_DESTROYED;
	}

	public function restart() {
		$this->destroy();

		if($this->state !== self::STATE_DESTROYED)
			throw new session_FailedToDestroyException;

		$this->state = self::STATE_RESTARTING;

		$this->storage->register();

		$this->start();
	}

	public function close() {
		session_write_close();
	}

	protected function configure($options = array()) {
		ini_set('session.save_handler', 'files');
		ini_set('session.use_trans_sid', false);

		if(isset($options['name']))
			session_name($options['name']);
		else
			session_name('atsumi_session');
	}

	protected function destroyCookie() {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	protected function generateId($len = 32) {
		static $validChars = '1234567890abcde';

		$id = '';
		while(strlen($id) < $len)
			$id .= $validChars[mt_rand(0, 14)];

		session_id($id);
		return $id;
	}
}
?>