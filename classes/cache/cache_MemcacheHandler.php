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
 * A wrapper class for Memcache cache Extention
 * @package		Atsumi.Framework
 * @subpackage	Cache
 * @since		0.90
 */
class cache_MemcacheHandler implements cache_HandlerInterface {
	/* CONSTANTS */

	/**
	 * Constant defining a minite storage duration
	 * @var integer
	 */
	const DURATION_MINUTE = 60;

	/* PROPERTIES */

	/**
	 * The Memcache object instance used to cache data
	 * @access private
	 * @var Memcache
	 */
	private $memcache = null;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new cache_ApcHandler instance
	 * @access public
	 * @param string $host The host where memcache is listening for connections
	 * @param integer $port The port where memcache is listening for connections
	 */
	public function __construct($host, $port) {
		$this->memcache = new Memcache;

		if(!$this->memcache->connect($host, $port))
			throw new cache_CouldNotConnectException('Could not connect to Memcache server');
	}

	/* GET METHODS */

	/**
	 * Retrieves a value by key from cache store
	 * @access public
	 * @param mixed $key The key by which the value was stored
	 * @param mixed $default A value to return if the variable does not exists [optional, default: null]
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return mixed The value stored under the key or, $default on failure
	 */
	public function get($key, $default = null, $namespace = 'default') {
		$result = $this->memcache->get($key);
		return(is_array($result) ? $result[0] : $default);
	}

	/* SET METHODS */

	/**
	 * Stores a value by key in the cache store
	 * @access public
	 * @param mixed $key The key to store the variable under
	 * @param mixed $data The variable to store
	 * @param integer $ttl How long to keep the variable alive or 0 for persistent [optional, default: 0]
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return boolen True on success or, False on failure
	 */
	public function set($key, $data, $ttl = 0, $namespace = 'default') {
		return $this->memcache->set($key, array($data), MEMCACHE_COMPRESSED, $ttl);
	}

	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Removes a value by key from the cache store
	 * @access public
	 * @param mixed $key The key to store the variable under
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return boolen True on success or, False on failure
	 */
	public function delete($key, $namespace = 'default') {
		return $this->memcache->delete($key);
	}

	/**
	 * Checks if a key exists within the cache store
	 * @access public
	 * @param mixed $key The key to store the variable under
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return boolen True on success or, False on failure
	 */
	public function exists($key, $namespace = 'default') {
		return is_array($this->memcache->get($key));
	}

	/**
	 * Clears the cache store of all stored values
	 * @access public
	 * @return boolen True on success or, False on failure
	 */
	public function flush() {
		return $this->memcache->flush();
	}
}
?>