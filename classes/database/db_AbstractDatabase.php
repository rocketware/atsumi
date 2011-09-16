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
 * Used by all vender databases to gain basic functionality and forces the implementation of the
 * database interface.
 * @package		Atsumi.Framework
 * @subpackage	Database
 * @since		0.90
 */
abstract class db_AbstractDatabase /* implements db_InterfaceDatabase */ {

	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * The main PDO object that is used for the underlying database access
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * Weather or not the database is currently connected
	 * @var bool
	 */
	protected $connected = false;

	/**
	 * Debug information about queries made by the class. Only populated in debug mode.
	 * @var array
	 */
	protected $queryTimes = array();

	/**
	 * Encription object used to encode and decode encripted data from the database.
	 * @var encrypt_Interface
	 */
	protected $encrypter;

	/**
	 * The number of rows affected by the last performed query.
	 * @var integer
	 */
	protected $affectedRows;

	/**
	 * Weather or not the database is in debug mode.
	 * @var bool
	 */
	protected $debug = true;

	/**
	 * Caster used to convert native types into database strict types.
	 * @var caster_Abstract
	 */
	protected $caster;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new db_AbstractDatabase instance.
	 */
	public function __construct($config = array()) {
		$this->connect($config);

		if(isset($config['encrypter'])) {
			if(is_string($config['encrypter'])) {
				$class = $config['encrypter'];
				$config['encrypter'] = new $class();
			}
		} else {
			$config['encrypter'] = new encrypt_Simple();
		}
		$this->encrypter = $config['encrypter'];

		atsumi_Debug::addDatabase($this);
		$this->initCaster();
	}

	/**
	 * Destructor
	 * TODO: Remove if not in use.
	 */
	/*public function __destruct() {
		$this->disconnect();
	}*/

	/* GET METHODS */

	/**
	 * Used to get query debug information.
	 * @return array An array of executed query times.
	 */
	public function getQueryTimes() {
		return $this->queryTimes;
	}

	/**
	 * Returns the encrypter used by the database when processing %h
	 * @return encrypt_Interface The encripter
	 */
	public function getEncrypter() {
		return $this->encrypter;
	}

	/**
	 * Returns the number of rows affected by the last query
	 * @return integer The number of rows
	 */
	public function getAffectedRows() {
		return $this->affectedRows;
	}

	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Abstract function used to initalise the correct caster for the database vender.
	 * @return caster_Abstract The caster to use.
	 */
	abstract protected function initCaster();

	/**
	 * Connection function called by child class. Child class should construct the connection string
	 * and then pass to this function for connection.
	 * @param $conString
	 * @param $config
	 * @return unknown_type
	 */
	protected function connectReal($conString, $config) {
		try {
			$username =(isset($config['username']) ? $config['username'] : null);
			$password =(isset($config['password']) ? $config['password'] : null);

			$this->pdo = new PDO($conString, $username, $password);

			$this->connected = true;
		}
		catch(PDOException $e) {
			throw new db_ConnectionFailedException(get_class($this), $e->getMessage());
		}
	}

	/**
	 * Internal function called to case native tyes into database stict types.
	 * @param mixed $args The variables to case.
	 * @throws db_Exception Thrown if the caster has not been set.
	 */
	protected function cast($args) {
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');

		return $this->caster->castString(func_get_args());
	}

	/**
	 * Closes the database connection and destoryies the internal pdo object.
	 */
	public function disconnect() {
		unset($this->pdo);
		$this->pdo = null;
		$this->connected = false;
	}

	/**
	 * Executes a basic query with casted variables
	 * @param string $query The query string
	 * @param mixed $args The args to be casted into the query string
	 * @param mixed $_ Arg repeater (Repeat the last arg as many times as needed)
	 */
	public function query($query, $args = null, $_ = null) {
		$args = func_get_args();
		return $this->queryReal(call_user_func_array(array(&$this, 'cast'), $args));
	}

	/**
	 * Actually executes a sql string on the connected database.
	 *
	 * Direct use is not recommened.
	 * @param string $sql
	 * @throws db_NoConnectionException Thrown if there is no database connection.
	 * @throws db_QueryFailedException Thrown if the query failed.
	 */
	public function queryReal($sql) {
		if(!$this->connected)
			throw new db_NoConnectionException('No Database Connection');

		if($this->debug)
			$startTime = time() + microtime();

		$this->affectedRows = null;

		try {
			$return = $this->pdo->query($sql);

			if($return === false) {
				$error = $this->pdo->errorInfo();
				throw new PDOException((isset($error[2]) ? $error[2] : 'Database Error: '.$error[0]));
			}

			$data = $return->fetchAll(PDO::FETCH_OBJ);

			if(!is_array($data))
				throw new PDOException('Failed to return data array');

			if($this->debug) {
				if (count($this->queryTimes) > 100) {
					$this->queryTimes = null;
					$this->debug = false;
					atsumi_Debug::record(
						'Too many queries to debug',
						'Atsumi has disabled query debugging for this script as too many queries were fired.',
						atsumi_Debug::AREA_DATABASE,
						null
					);
				} else
					$this->queryTimes[] = array(
						'sql'			=> $sql,
						'time'			=>(time() + microtime()) - $startTime,
						'row_count'		=> count($data)
					);
			}

			return $data;
		} catch(PDOException $e) {
			throw new db_QueryFailedException($e->getMessage() . "<br />" . $sql);
		}
	}

	public function select($_) {
		$args = func_get_args();
		return call_user_func_array(array(&$this, 'query'), $args);
	}

	/**
	 * Performs a query, returning a single result
	 * @param $colums string The colum or colums to select
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_ Arg repeater (Repeat the last arg as many times as needed)
	 * @return The query results
	 */
	public function selectOne($colums, $tables, $where, $args = null, $_ = null) {
		$args = func_get_args();
		$result = call_user_func_array(array(&$this, 'select'), $args);

		if(count($result) > 1)
			throw new db_QueryFailedException('selectOne returned more than one result');

		return array_key_exists(0, $result) ? $result[0] : null;
	}

	/**
	 * Performs a query, returning all colums of one result in an array
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_ Arg repeater (Repeat the last arg as many times as needed)
	 * @return The query results
	 */
	public function fetchOne($tables, $where, $args = null, $_ = null) {
		$args = func_get_args();
		array_unshift($args, '*');

		return call_user_func_array(array(&$this, 'selectOne'), $args);
	}

	/**
	 * Performs a insert query
	 *
	 * Example:
	 *  $db->insert('tableA', 'column1 = %s', 'a string', 'column2 = %i', 12345);
	 *
	 * @param string $table The table to insert into
	 * @param string $column Column to insert into
	 * @param string $value Value to insert into
	 * @param $_ Arg repeater (Repeat the last args as many times as needed)
	 * @throws db_Exception If the number of values does not match the number of columns
	 */
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

		/* parse values */
		$valueString = implode(', ', $types);
		array_unshift($values, $valueString);
		$valueString = call_user_func_array(array($this, 'parse'), $values);


		$query = $this->parse(
			'INSERT INTO %@ (%l) VALUES(%l)', $table, implode(', ', $names), $valueString
		);

		$this->queryReal($query);
	}

	// TODO: public function insert($table, $column, $value = null, $_ = null)

	public function update($args) {
		/* parse the query */
		$args = func_get_args();
		$query = call_user_func_array (array ($this, 'parseUpdateQuery'), $args);

		/* perform query */
		$this->query('%l', $query);

		return true;
	}

	public function updateOne($args) {
		/* call update */
		$args = func_get_args ();
		$ret = call_user_func_array (array ($this, 'update'), $args);

		// ensure affected row count is correct
		if($this->affected_rows () == 0)
			throw new sql_Exception ('No rows affected in updateOne()');
		if($this->affected_rows () > 1)
			throw new sql_Exception ('Multiple rows affected in updateOne()');

		return true;
	}

	public function parseUpdateQuery($args) {
		$args = func_get_args ();
		$sets = $this->quotef_special($args);
		$update = array_shift ($sets);
		$where = array_shift ($sets);
		if (count ($sets) == 0) $sets = array ($where);

		/* return update query string */
		return $this->format(
			'UPDATE %l SET %l WHERE %l', $update, implode(', ', $sets), $where
		);
	}

	/* DEPRECATED METHODS */

	/**
	 * Selects a single record from the database
	 * @deprecated Back compatability functions. DO NOT USE!
	 * @param unknown_type $_
	 */
	public function select_1($_) {
		$args = func_get_args ();
		return call_user_func_array (array (&$this, 'selectOne'), $args);
	}

	/**
	 * Updates a single record in the database.
	 * @deprecated Back compatability functions. DO NOT USE!
	 * @param unknown_type $_
	 */
	public function update_1($_) {
		$args = func_get_args ();
		return call_user_func_array (array (&$this, 'updateOne'), $args);
	}
}
?>