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
 * A wrapper class for Alternative PHP Cache(APC) cache Extention
 * @package		Atsumi.Framework
 * @subpackage	Cache
 * @since		0.90
 */
class cache_ApcHandler extends cache_AbstractHandler {
	/* CONSTANTS */

	const DEFAULT_TTL	= 0;

	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new cache_ApcHandler instance
	 * @access public
	 */
	protected function __construct($useExceptions = false) {
		$this->useExceptions = $useExceptions;

		if(!functions_exist('apc_store', 'apc_fetch', 'apc_delete', 'apc_exists', 'apc_clear_cache'))
			throw new cache_ExtensionMissing('Failed to detect APC cache Extention');
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
	protected function _get($key, $default = null, $namespace = 'default') {
		$success = false;
		$return = apc_fetch($key, $success);

		if(!$success) {
			if($this->useExceptions)
				throw new cache_NotFoundException(sf('Could not fine \'%s\' variable', $key));

			$return = $default;
		}

		return $return;
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
	protected function _set($key, $data, $ttl = 0, $namespace = 'default') {
		return apc_store($key, $data, $ttl);
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
	protected function _delete($key, $namespace = 'default') {
		return apc_delete($key);
	}

	/**
	 * Checks if a key exists within the cache store
	 * @access public
	 * @param mixed $key The key to store the variable under
	 * @param string $namespace The namespace under which the variable is stored [optional, default: 'default']
	 * @return boolen True on success or, False on failure
	 */
	protected function _exists($key, $namespace = 'default') {
		return apc_exists($key);
	}

	/**
	 * Clears the cache store of all stored values
	 * @access public
	 * @return boolen True on success or, False on failure
	 */
	protected function _flush() {
		return apc_clear_cache('user');
	}
}
?>