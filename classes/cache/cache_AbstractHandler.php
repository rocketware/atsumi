<?php
/**
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * A base abstract class for cache handlers
 * @package		Atsumi.Framework
 * @subpackage	Cache
 * @since		0.90
 */
abstract class cache_AbstractHandler implements cache_HandlerInterface {

	/* methods to be set in the cache handler */
	abstract protected function _get($key, $default, $namespace);
	abstract protected function _set($key, $data, $ttl, $namespace);
	abstract protected function _delete($key, $namespace);
	abstract protected function _exists($key, $namespace);
	abstract protected function _flush();

	/**
	 * Retrieves a value by key from cache store
	 * @access public
	 * @param mixed $key The key by which the value was stored
	 * @param mixed $default A value to return if the variable does not exists [optional, default: null]
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return mixed The value stored under the key or, $default on failure
	 */
	final public function get($key, $default = null, $namespace = 'default') {
		try {
			return $this->_get($key, $default, $namespace);
		} catch (Exception $e) {
			atsumi_Debug::record('Cache Handler failed to get data (key: '.$key.')',
								$e->getMessage(), false, atsumi_Debug::AREA_CACHE);
			return false;
		}
	}

	/**
	 * Stores a value by key in the cache store
	 * @access public
	 * @param mixed $key The key to store the variable under
	 * @param mixed $data The variable to store
	 * @param integer $ttl How long to keep the variable alive or 0 for persistent [optional, default: 0]
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return boolen True on success or, False on failure
	 */
	final public function set($key, $data, $ttl = 0, $namespace = 'default') {
		try {
			return $this->_set($key, $data, $ttl = 0, $namespace);
		} catch (Exception $e) {
			atsumi_Debug::record('Cache Handler failed to set data (key: '.$key.')',
								$e->getMessage(), false, atsumi_Debug::AREA_CACHE);
			return false;
		}
	}

	/**
	 * Removes a value by key from the cache store
	 * @access public
	 * @param mixed $key The key to store the variable under
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return boolen True on success or, False on failure
	 */
	final public function delete($key, $namespace = 'default') {
		try {
			return $this->_delete($key, $namespace);
		} catch (Exception $e) {
			atsumi_Debug::record('Cache Handler failed to delete data (key: '.$key.')',
								$e->getMessage(), false, atsumi_Debug::AREA_CACHE);
			return false;
		}
	}


	/**
	 * Checks if a key exists within the cache store
	 * @access public
	 * @param mixed $key The key to store the variable under
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return boolen True on success or, False on failure
	 */
	final public function exists($key, $namespace = 'default') {
		try {
			return $this->_exists($key, $namespace);
		} catch (Exception $e) {
			atsumi_Debug::record('Cache Handler failed lookup (key: '.$key.')',
								$e->getMessage(), false, atsumi_Debug::AREA_CACHE);
			return false;
		}
	}

	/**
	 * Clears the cache store of all stored values
	 * @access public
	 * @return boolen True on success or, False on failure
	 */
	final public function flush() {
		try {
			return $this->_flush();
		} catch (Exception $e) {
			atsumi_Debug::record('Cache Handler failed to flush',
								$e->getMessage(), false, atsumi_Debug::AREA_CACHE);
			return false;
		}
	}
}
?>