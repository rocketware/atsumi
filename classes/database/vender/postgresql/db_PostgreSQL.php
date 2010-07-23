<?php

/**
 *
 * @package Database
 * @subpackage Vender.PostreSql
 */
class db_PostgreSql extends db_AbstractDatabase {

	protected function initParser () {
		$this->parser = new parser_PostgreSQL();
	}

	/**
	 * PostgreSql Connect function, config options are:
	 *
	 * host			: The hostname on which the database server resides
	 * port			: The port number where the database server is listening
	 * dbname		: The name of the database
	 * username		: The name of the user for the connection
	 * password		: The password of the user for the connection
	 */
	public function connect($config = array()) {
		$host =(isset($config['host']) ? $config['host'] : null);
		$port =(isset($config['port']) ? $config['port'] : null);
		$dbname =(isset($config['dbname']) ? $config['dbname'] : null);

		$conString = sf(
			'pgsql:%s%s%s',
			(!is_null($host) ? sf(' host=%s', $host) : ''),
			(!is_null($port) ? sf(' port=%s', $port) : ''),
			(!is_null($dbname) ? sf(' dbname=%s', $dbname) : '')
		);

		$this->connectReal($conString, $config);
	}


	/* TRANSACTION FUNCTIONS */

	/**
	 * Returns true if the database supports transaction
	 *
	 * @return boolean If the database supports transactions
	 */
	public function transactionSupport() {
		return true;
	}

	/**
	 * Returns true if the database is in transaction
	 *
	 * @return boolean If the database is in transaction
	 */
	public function transaction() {
		return $this->transaction;
	}

	/**
	 * Begins a database transaction. Will fail if there is already
	 * a transaction in progress
	 *
	 * @return boolean If beginning the transaction was successful
	 */
	public function transactionBegin() {
		if($this->transaction)
			throw new db_Exception('Cannot call beginTransaction() while already in transaction');

		$this->query('BEGIN');
		$this->transaction = true;
		return true;
	}

	/**
	 * Commits a database transaction. Will fail if there is no
	 * transaction in progress
	 *
	 * @return boolean If commiting the transaction was successful
	 */
	public function transactionCommit() {
		if(!$this->transaction)
			throw new db_Exception('Cannot call commitTransaction() while not in transaction');

		$this->transaction = false;
		return $this->query('COMMIT');
	}

	/**
	 * Rolls back a database transaction. Will fail if there is no
	 * transaction in progress
	 *
	 * @return boolean If rolling back the transaction was successful
	 */
	public function transactionRollback() {
		if(!$this->transaction)
			throw new db_Exception('Cannot call rollbackTransaction() While not in transaction');

		$this->transaction = false;
		return $this->query('ROLLBACK');
	}

	/**
	 * Rolls back a database transaction, if there is one in
	 * progress. This is designed for use in catch blocks to ensure
	 * a rollback in case of error
	 *
	 * @return null
	 */
	public function transactionAutoRollback() {
		if($this->transaction)
			$this->rollbackTransaction();
	}


}
?>