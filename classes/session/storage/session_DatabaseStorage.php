<?php

class session_DatabaseStorage extends session_AbstractStorage {

	protected $database = null;

	public function __construct($options) {
		if(!isset($options['database']))
			throw new Exception('A database is needed for this storage method');

		$this->database = $options['database'];

		ini_set('session.gc_divisor', 1);
		ini_set('session.gc_maxlifetime', 1440);
		ini_set('session.gc_probability', 100);

		parent::__construct($options);
	}

	public function read($id) {

		try {
			$result = $this->database->select_1('SELECT * FROM session WHERE checksum = %i AND session_id = %s', crc32($id), $id);

		}
		catch(Exception $e) {
			Atsumi::debug__log(
				'Session Failed to load from DB',
				sf('The session(%s) session could not load', $id),
				atsumi_Debugger::AREA_SESSION,
				$e
			);
			return serialize(array($e->getMessage()));
		}

		if(is_null($result))
			return '';

		Atsumi::debug__log(
			'Session loaded from DB',
			sf('The session loaded from the db.' ),
			atsumi_Debugger::AREA_SESSION,
			$result
		);
		return base64_decode($result->s_data);
	}

	public function write($id, $sessionData) {

		if($this->database->exists('session', 'checksum = %i AND session_id = %s', crc32($id), $id)) {


			Atsumi::debug__log(
				'Updating Session',
				sf('The session(%s) is being updated to the DB', $id ),
				atsumi_Debugger::AREA_SESSION,
				base64_encode($sessionData)
			);

			$this->database->update(
				'session',
				'checksum = %i AND session_id = %s', crc32($id), $id,
				'data = %s', base64_encode($sessionData),
				'last_active	= NOW()'
			);
		}
		else {

			Atsumi::debug__log(
				'Inserting Session',
				sf('The atsumi(%s) is being inserted to the DB: ', $id ),
				atsumi_Debugger::AREA_SESSION,
				$sessionData
			);

			$this->database->insert(
				'session',
				'checksum		= %i', crc32($id),
				'session_id		= %s', $id,
				'data			= %s', $sessionData,
				'last_active	= NOW()'
			);
		}
		return true;
	}

	public function destroy($id) {
		$this->database->delete(
			'session',
			'checksum = %i AND session_id = %s', crc32($id), $id
		);

		return true;
	}

	public function gc($maxlifetime) {
		$this->database->delete(
			'session',
			'last_active < %t',(time() - $maxlifetime)
		);

		return true;
	}
}