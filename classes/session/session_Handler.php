<?php
/**
 * File defines the constants, properties and methods of the session_Handler class.
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative of works licensed
 * under the GNU General Public License or other free or open source software licenses.
 * See license.txt for license notices and details.
 */

/**
 * The main session managment class.
 * @package		Atsumi.Framework
 * @subpackage	Cache
 * @since		0.90
 */
class session_Handler {
	/* CONSTANTS */

	/**
	 * The default storage medium.
	 * @var string
	 */
	const DEFAULT_STORAGE		= 'session_BasicStorage';

	/**
	 * The default namespace.
	 * @var string
	 */
	const DEFAULT_NAMESPACE		= 'default';

	/**
	 * The session namespace used to store session related data.
	 * @var string
	 */
	const SESSION_NAMESPACE		= 'session';

	/* Possible States */
	const STATE_ACTIVE			= 1; // The session is active and alive
	const STATE_EXPIRED			= 2; // The session has passed its expiry time
	const STATE_RESTARTING		= 3; // The session is being reset
	const STATE_DESTROYED		= 4; // The session has been killed
	const STATE_ERROR			= 5; // The session is in error!

	/* PROPERTIES */

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

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Constructor - Must use getInstance to get a session singalton object
	 */
	protected function __construct($options = array()) {
		// Start timer for constructor compleate time
		atsumi_Debug::startTimer();

		$this->configure($options);

		// Fetch the session storage handler
		$storage = (isset($options['storage']) ? $options['storage'] : self::DEFAULT_STORAGE);

		if(is_subclass_of($storage, 'session_AbstractStorage'))
			$this->storage = new $storage($options);

		// Start the session
		$this->start($options);

		// Add debug information
		atsumi_Debug::record(
			'Session Created',
			'The atsumi session constructor completed', null, true,
			atsumi_Debug::AREA_SESSION
		);
	}

	/**
	 * Destructor - Will end and write the session if destroyed
	 */
	public function __destruct() {
		try {
			$this->close();
		} catch (Exception $e) { }
	}

	/* GET METHODS */

	/**
	 * Returns a singleton instance of atsumi session
	 * @param $options Session setup options
	 * @return object A session_Handler object
	 */
	public static function getInstance($options = array()) {
		static $instance;

		if(!is_object($instance))
			$instance = new self($options);

		return $instance;
	}

	/**
	 * Returns a variable from the current active session.
	 * @param string $name The name of the variable to return.
	 * @param mixed $default The variable to return if the value does not exist.
	 * @param string $namespace The namespace under which the variable is stored.
	 * @return mixed The variable under that name or the default.
	 */
	public function &get($name, $default = null, $namespace = self::DEFAULT_NAMESPACE) {
		// Add prefix to prevent namespace collisions
		$namespace = '__'.$namespace;

		if(isset($_SESSION[$namespace][$name]))
			return $_SESSION[$namespace][$name];

		return $default;
	}

	/**
	 * Returns the current session id.
	 * @return string The session id.
	 */
	public function getId() {
		return session_id();
	}

	/* SET METHODS */

	/**
	 * Sets a variable to the current active session and return the old value if set.
	 * @param string $name The name of the variable to return.
	 * @param mixed $value The variable to be stored under that name.
	 * @param string $namespace The namespace under which the variable is stored.
	 * @return mixed The old value under the name if it exists.
	 */
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

	/* MAGIC METHODS */
	/* METHODS */

	public function push($name, $value, $namespace = self::DEFAULT_NAMESPACE) {
		// Add prefix to prevent namespace collisions
		$namespace = '__'.$namespace;

		if(!isset($_SESSION[$namespace][$name]) || !is_array($_SESSION[$namespace][$name]))
			throw new Exception ("Target not array");
		else
			$_SESSION[$namespace][$name][] = $value;

	}

	public function exists($name, $namespace = self::DEFAULT_NAMESPACE) {
		// Add prefix to prevent namespace collisions
		$namespace = '__'.$namespace;
		return isset($_SESSION[$namespace][$name]);
	}

	public function delete($name, $namespace = self::DEFAULT_NAMESPACE) {
		$this->set($name, null, $namespace);
	}

	protected function start($options = array()) {
		if(headers_sent() === true)
			throw new session_HeadersSentException;

		if($this->state === self::STATE_RESTARTING)
			$this->generateId();

		session_cache_limiter('none');
		session_start();

		if(isset($options['persistent']) && $options['persistent'] === true)
			$this->renewCookie($options['life']);

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

		if(isset($options['life'])) ini_set('session.cookie_lifetime', $options['life']);

		$this->configCookie($options);

		if(isset($options['name']))
			session_name($options['name']);
		else
			session_name('atsumi_session');
	}

	public function configCookie($options = array()) {
		$old = session_get_cookie_params();
		session_set_cookie_params(
			(isset($options['life'])			? $options['life']				: $old['lifetime']),
			(isset($options['cookie_path'])		? $options['cookie_path']		: $old['path']),
			(isset($options['cookie_domain'])	? $options['cookie_domain']		: $old['domain']),
			(isset($options['cookie_secure'])	? $options['cookie_secure']		: $old['secure']),
			(isset($options['cookie_httponly'])	? $options['cookie_httponly']	: $old['httponly'])
		);
	}

	protected function destroyCookie() {
		$name = session_name();
		if(!isset($_COOKIE[$name])) return;

		$params = session_get_cookie_params();
		setcookie($name, '', time() - 42000,
			$params['path'], $params['domain'],
			$params['secure'], $params['httponly']
		);
	}

	protected function renewCookie($life = 0) {
		$name = session_name();
		if(!isset($_COOKIE[$name])) return;

		$params = session_get_cookie_params();
		setcookie(
			$name,
			session_id(),
			($life > 0 ? (time() + $life) : 0),
			$params['path'],
			$params['domain'],
			$params['secure'],
			$params['httponly']
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

	/* DEPRECATED METHODS */

	public function has($name, $namespace = self::DEFAULT_NAMESPACE) {
		$this->exists($name, $namespace);
	}

	public function del($name, $namespace = self::DEFAULT_NAMESPACE) {
		$this->delete($name, $namespace);
	}
}
?>