<?php

/**
 *
 * @package Database
 */
interface db_InterfaceDatabase {

	/* TRANSACTION FUNCTIONS */

	/**
	 * Returns true if the database supports transaction
	 *
	 * @return boolean If the database supports transactions
	 */
	public function transactionSupport();

	/**
	 * Returns true if the database is in transaction
	 *
	 * @return boolean If the database is in transaction
	 */
	public function transaction();

	/**
	 * Begins a database transaction. Will fail if there is already
	 * a transaction in progress
	 *
	 * @return boolean If beginning the transaction was successful
	 */
	public function transactionBegin();

	/**
	 * Commits a database transaction. Will fail if there is no
	 * transaction in progress
	 *
	 * @return boolean If commiting the transaction was successful
	 */
	public function transactionCommit();

	/**
	 * Rolls back a database transaction. Will fail if there is no
	 * transaction in progress
	 *
	 * @return boolean If rolling back the transaction was successful
	 */
	public function transactionRollback();

	/**
	 * Rolls back a database transaction, if there is one in
	 * progress. This is designed for use in catch blocks to ensure
	 * a rollback in case of error
	 *
	 * @return null
	 */
	public function transactionAutoRollback();

	/* GENERAL FUNCTIONS */

	/**
	 * Performs any query returning the results as an array This
	 * allows multiple result sets to be returned
	 *
	 * @param string $queryStr The sql string to be processed
	 * @param mixed $args Any args that should be processed into the sql string
	 */
	public function query($query, $args = null, $_ = null);

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
	public function select($columns, $tables, $where = null, $args = null, $_ = null);

	/**
	 * Performs a query, returning a single result
	 *
	 * @param $colums string The colum or colums to select
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return The query results
	 */
	public function selectOne($columns, $tables, $where, $args = null, $_ = null);

	/**
	 * Performs a query, returning all colums of all results in an array
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return The query results
	 */
	public function fetch($tables, $where = null, $args = null, $_ = null);

	/**
	 * Performs a query, returning all colums of one result in an array
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return The query results
	 */
	public function fetchOne($tables, $where, $args = null, $_ = null);

	/**
	 * Checks for the existance of a record
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return boolean If the row exists
	 */
	public function exists($tables, $where = null, $args = null, $_ = null);

	/**
	 * Counts the number of records
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return integer The number of records
	 */
	public function count($tables, $where = null, $args = null, $_ = null);

	/* INSERT/UPDATE/DELETE QUERIES */

	/**
	 * Performs an insert query. The parameters consist of the table name,
	 * followed by a series of update-like expressions
	 *
	 * Example:
	 * 		$valueForColumnA = 'a string';
	 *
	 * 		$db->insert(
	 * 			'table_name.table',
	 * 			'column_a = %s',  $valueForColumnA,
	 * 			'column_b = %i',  542165,
	 * 			'column_c = %b',  true
	 * 		);
	 *
	 * @param $table string The table or tables to query from
	 * @param $column string Column name followed by = followed by value type
	 * @param $value mixed The value of the last specified column
	 * @param $_
	 * @return The query result
	 */
	public function insert($table, $column, $value = null, $_ = null);

	public function insertIfNotExists($table, $where, $args = null, $_ = null);

	public function insertOrUpdateOne($table, $where, $args = null, $_ = null);

	/**
	 * This inserts multiple rows and should always be much faster than
	 * calling insert many times. Where possible it should use any underlying
	 * database features to further increase speed.
	 *
	 * If inserting whole table rows use bulk_insert_table() which may be
	 * faster.
	 */
	public function insertBulk($table, $columns, $data);

	/**
	 * Performs an update query. The parameters consist of the table name,
	 * the where clause, and a series of set expressions
	 *
	 * Example:
	 * 		$valueForColumnA = 'a string';
	 *
	 * 		$db->update(
	 * 			'table_name.table',
	 * 			'id = %i AND name = %s', $id, $name,
	 * 			'column_a = %s',  $valueForColumnA,
	 * 			'column_b = %i',  542165,
	 * 			'column_c = %b',  true
	 * 		);
	 *
	 * @param $table string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @param $column string Column name followed by = followed by value type
	 * @param $value mixed The value of the last specified column
	 * @param $_
	 * @return The query result
	 */
	//public function update($table, $where, $args = null, $_ = null, $column = null, $value = null, $_ = null);

	/**
	 * Calls update and ensures that the number of affected rows is exactly one
	 *
	 * Example:
	 * 		$valueForColumnA = 'a string';
	 *
	 * 		$db->update(
	 * 			'table_name.table',
	 * 			'id = %i AND name = %s', $id, $name,
	 * 			'column_a = %s',  $valueForColumnA,
	 * 			'column_b = %i',  542165,
	 * 			'column_c = %b',  true
	 * 		);
	 *
	 * @param $table string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @param $column string Column name followed by = followed by value type
	 * @param $value mixed The value of the last specified column
	 * @param $_
	 * @return The query result
	 */
	//public function updateOne($table, $where, $args = null, $_ = null, $column = null, $value = null, $_ = null);

	/**
	 * Performs a delete query.
	 *
	 * @param $table string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return void
	 */
	public function delete($table, $where, $args = null, $_ = null);

	/**
	 * Performs a delete query and ensures that the number of affected rows is exactly 1.
	 *
	 * @param $table string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return void
	 */
	public function deleteOne($table, $where, $args = null, $_ = null);

	public function deleteAndInsert($table, $where, $args = null, $_ = null);

	/* DATA DEFINITION */

	/**
	 * Creates a table and optionally inserts data
	 *
	 * @param unknown_type $table
	 * @param unknown_type $columns
	 * @param unknown_type $data
	 * @return void
	 */
	public function tableCreate($table, $columns, $data = null);

	/**
	 * Drops a table
	 *
	 * @param unknown_type $table
	 * @return void
	 */
	public function tableDrop($table);
}
?>