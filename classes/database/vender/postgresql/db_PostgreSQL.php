<?php

/**
 *
 * @package Database
 * @subpackage Vender.PostreSql
 */
class db_PostgreSql extends db_AbstractDatabase {

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

	/* CHARACTER ESCAPE FUNCTIONS */

	/**
	 * Transforms a array into a query literal.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted array.
	 */
	protected function a($val, $nullValid = false) {
		return $val;
	}

	/**
	 * Transforms a boolean into a query literal.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted boolean.
	 */
	protected function b($val, $nullValid = false) {
		return sf("'%s'::boolean", $val);
	}

	/**
	 * Transforms a date into a query literal.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted date.
	 */
	protected function d($val, $nullValid = false) {
		return $val;
	}

	/**
	 * Transforms a string into a query literal ecripted representation.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped, quoted and encripted string.
	 */
	protected function e($val, $nullValid = false) {
		return $val;
	}

	/**
	 * Transforms a float into a query literal.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted float.
	 */
	protected function f($val, $nullValid = false) {
		return $val;
	}

	/**
	 * Transforms a string into a query literal hash.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped, quoted and hashed string.
	 */
	protected function h($val, $nullValid = false) {
		return $val;
	}

	/**
	 * Transforms an integer into a query literal.
	 * @param int $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted integer.
	 */
	protected function i($val, $nullValid = false) {
		return sf("'%s'::integer", $val);
	}

	/**
	 * Transforms a string into a query literal.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted string.
	 */
	protected function s($val, $nullValid = false) {
		return sf("'%s'", $val);
	}

	/**
	 * Transforms a timestamp into a query literal.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted timestamp.
	 */
	protected function t($val, $nullValid = false) {
		return sf("'%s'::timestamp with time zone", $val);
	}
	
	/**
	 * Transforms binary string into a query literal.
	 * @param string $val The value to escape.
	 * @param bool $nullValid If the value can be null
	 * @return string The escaped and quoted timestamp.
	 */
	protected function x($val, $nullValid = false) {
		return sf("'%s'::bytea", $val);
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

	/* SELECT QUERIES */

	/**
	 * Performs a query, returning all results in an array
	 *
	 * @param $colums string The colum or colums to select
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return The query results
	 */
	public function select($colums, $tables, $where = null, $args = null, $_ = null) {
		$args = func_get_args();

		$colums = array_shift($args);
		$tables = array_shift($args);

		// If there are still args left we have a where clause and possible where args
		if(count($args) > 0)
			$where = array_shift($args);

		if(is_array($colums))
			$colums = implode(', ', $colums);

		$query = sf(
			'SELECT %s FROM %s%s',
			$colums,
			$tables,
			(isset($where) ? sf(' WHERE %s', $where) : '')
		);

		array_unshift($args, $query);

		return call_user_func_array(array(&$this, 'query'), $args);
	}

	/**
	 * Performs a query, returning all colums of all results in an array
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return The query results
	 */
	public function fetch($tables, $where = null, $args = null, $_ = null) {
		$args = func_get_args();

		array_unshift($args, '*');

		return call_user_func_array(array(&$this, 'select'), $args);
	}

	/**
	 * Checks for the existance of a record
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return boolean If the row exists
	 */
	public function exists($tables, $where = null, $args = null, $_ = null) {
		$args = func_get_args();

		$tables = array_shift($args);

		// If there are still args left we have a where clause and possible where args
		if(count($args) > 0)
			$where = array_shift($args);

		$query = sf(
			'SELECT EXISTS(SELECT * FROM %s%s) AS \'exists\'',
			$tables,
			(isset($where) ? sf(' WHERE %s', $where) : '')
		);

		array_unshift($args, $query);

		$result = call_user_func_array(array(&$this, 'query'), $args);

		return(bool)$result[0]->exists;
	}

	/**
	 * Counts the number of records
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return integer The number of records
	 */
	public function count($tables, $where = null, $args = null, $_ = null) {
		$args = func_get_args();

		array_unshift($args, 'COUNT(*) as count');

		$result = call_user_func_array(array(&$this, 'select'), $args);

		return(int)$result[0]->count;
	}

	/* INSERT/UPDATE/DELETE QUERIES */

	public function insert($table, $column, $value = null, $_ = null) {
		$args = func_get_args();

		$table = array_shift($args);

		// If there are still args left we have variable pairs
		$names = array();
		$types = array();
		$values = array();
		while(count($args) >= 2) {
			$column = array_shift($args);

			$column = explode('=', $column, 2);

			$names[]	= trim($column[0]);
			$types[]	= trim($column[1]);
			$values[]	= array_shift($args);
		}

		if(count($args) > 0)
			throw new db_Exception('Uneven number of column value paires');

		$query = sf(
			'INSERT INTO %s(%s) VALUES(%s)',
			$table,
			implode(', ', $names),
			implode(', ', $types)
		);

		array_unshift($values, $query);

		return call_user_func_array(array(&$this, 'query'), $values);
	}

	public function update($table, $where, $args = null, $_ = null, $column = null, $value = null, $_ = null) {}

	public function updateOne($table, $where, $args = null, $_ = null, $column = null, $value = null, $_ = null) {}

	public function delete($table, $where, $args = null, $_ = null) {}

	public function deleteOne($table, $where, $args = null, $_ = null) {}

	public function insertIfNotExists($table, $where, $args = null, $_ = null) {}

	public function insertOrUpdateOne($table, $where, $args = null, $_ = null) {}

	public function deleteAndInsert($table, $where, $args = null, $_ = null) {}

	/* BULK INSERT */

	public function insertBulk($table, $columns, $data) {}

	/* DATA DEFINITION */

	public function tableCreate($table, $columns, $data = null) {}

	public function tableDrop($table) {}
}
?>