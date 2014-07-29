<?php
/**
 * Created by PhpStorm.
 * User: jimmyff
 * Date: 30/04/2014
 * Time: 14:42
 * Description: Storage for settings that need to be passed around but has no persistent back end
 * This can be used as a portable settings store that expires at end of the request if not saved elsewhere
 */

class cache_TemporaryStorageHandler extends cache_AbstractHandler {

	// null ttl: doesn't expire
	const DEFAULT_TTL	= null;

	private $store 		= array();

	public function __construct() {

	}

	protected function _get($key, $default = null, $namespace = 'default'){

		if (isset($this->store[$namespace][$key]) &&
			(is_null($this->store[$namespace][$key]['expires']) || $this->store[$namespace][$key]['expires'] > time()))
				return $this->store[$namespace][$key]['value'];

		else return $default;

	}
	protected function _set($key, $data, $ttl = null, $namespace = 'default') {

		if (!isset($this->store[$namespace])) {
			$this->store[$namespace] = array();
		}

		$this->store[$namespace][$key] = array(
			'value'		=> $data,
			'expires'	=> is_null($ttl)?null:(time() + $ttl)
		);

	}
	protected function _delete($key, $namespace = 'default') {

		if (isset($this->store[$namespace][$key]));
			unset($this->store[$namespace][$key]);

	}
	protected function _exists($key, $namespace = 'default'){

		return isset($this->store[$namespace][$key]) &&
			(is_null($this->store[$namespace][$key]['expires']) || $this->store[$namespace][$key]['expires'] > time());
	}

	protected function _flush() {
		$this->store 	= array();
	}

}

?>