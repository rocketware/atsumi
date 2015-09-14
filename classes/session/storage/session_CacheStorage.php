<?php

class session_CacheStorage extends session_AbstractStorage {

	const DEFAULT_MAX_LIFE = 7200; // 2 hours
	
	protected $cache = null;

	public function __construct(cache_AbstractHandler $cache, $options = array()) {
		
		$this->cache = $cache;
		$this->ttl = (isset($options['life']) ? $options['life'] : SELF::DEFAULT_MAX_LIFE);

		ini_set('session.gc_divisor', 1);
		ini_set('session.gc_maxlifetime', $this->ttl);
		ini_set('session.gc_probability', 100);

		parent::__construct($options);
	}

	public function read($id) {

		atsumi_Debug::startTimer('SESSION_READ');
		
		$result = $this->cache->get($id, null, 'SESSION');

		if(is_null($result))
			return '';

		atsumi_Debug::record(
			'Session loaded from cache',
			sf('Session ID: %s', $id),
			$result,
			'SESSION_READ',
			atsumi_Debug::AREA_SESSION,
			$result
		);
		return $result;
	}
	
	public function write($id, $sessionData) {
	
		$this->cache->set($id, $sessionData, $this->ttl, 'SESSION');
		return true;
	}

	public function destroy($id) {
		$this->cache->set($id, null, -1, 'SESSION');
		return true;
	}

	public function gc($maxlifetime) {
		return true;
	}
}