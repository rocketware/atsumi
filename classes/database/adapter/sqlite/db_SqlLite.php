<?php

/**
 *
 * @package Database
 * @subpackage Vender.SqlLite
 */
class db_SqlLite extends db_AbstractDatabase {

	/**
	 * PostgreSql Connect function, config options are:
	 *
	 * version	: SQLite3: 3, SQLite2: 2
	 * path		: Absolute path to the file store(memory used if not set)
	 */
	public function connect($config = array()) {

		if(isset($config['version']) && $config['version'] == '2') {
			$version = 'sqlite2';
			$extention = '.sq2';
		}
		else {
			$version = 'sqlite';
			$extention = '.sq3';
		}
		$storage =(isset($config['path']) ? $config['path'].$extention : ':memory:');

		$conString = sf('%s:%s', $version, $storage);

		$this->_connect($conString, $config);
	}
}
?>