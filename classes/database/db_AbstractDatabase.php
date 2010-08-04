<?php

/**
 * Used by all vender databases to gain basic functionality and forces
 * the implementation of the database interface.
 * @package Database
 */
abstract class db_AbstractDatabase /* implements db_InterfaceDatabase */ {

	/* The database connection object */
	protected $pdo;
	protected $connected = false;
	protected $queryTimes = array();
	protected $encrypter;
	protected $affectedRows;
	protected $debug = true;
	protected $parser;
	
	abstract protected function initParser ();
	/**
	 * Constructor
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
		$this->initParser();
	}

	protected function parse ($args) {
		if (is_null($this->parser))
			throw new db_Exception ('Parser not loaded.');
		
		return $this->parser->parseString(func_get_args());
	}

	/**
	 * Destructor
	 */
	//public function __destruct() {
		//$this->disconnect();
	//}

	/**
	 * Used to get query debug information
	 */
	public function getQueryTimes() {
		return $this->queryTimes;
	}

	/**
	 * Returns the encrypter used by the database when processing %h
	 */
	public function getEncrypter() {
		return $this->encrypter;
	}

	/**
	 * Returns the number of rows affected by the last query
	 *
	 * @return integer The number of rows
	 */
	public function getAffectedRows() {
		return $this->affectedRows;
	}

	/**
	 * Connection function called by child class. Child class should construct
	 * the connection string and then pass to this function for connection.
	 *
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

	public function disconnect() {
		unset($this->pdo);
		$this->pdo = null;
		$this->connected = false;
	}

	public function query($query, $args = null, $_ = null) {
		return $this->queryReal(call_user_func_array(array(&$this, 'parse'), func_get_args()));
	}

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

	/* SELECT QUERIES */

	public function select($columns, $tables, $where, $args = null, $_ = null) {
		
		$args = func_get_args();

		$colums = array_shift($args);
		$tables = array_shift($args);

		// If there are still args left we have a where clause and possible where args
		if(count($args) > 0)
			$where = array_shift($args);

		if(is_array($colums))
			$colums = implode(', ', $colums);

		$query = $this->parse(
			'SELECT %l FROM %@%l',
			$colums,
			$tables,
			(isset($where) ? $this->parse(' WHERE %l', $where) : '')
		);

		array_unshift($args, $query);

		return call_user_func_array(array(&$this, 'query'), $args);
	}
	
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
	public function selectOne($colums, $tables, $where, $args = null, $_ = null) {
		$args = func_get_args();
		$result = call_user_func_array(array(&$this, 'select'), $args);

		if(count($result) > 1)
			throw new db_QueryFailedException('selectOne returned more than one result');

		return array_key_exists(0, $result) ? $result[0] : null;
	}

	/**
	 * Performs a query, returning all colums of one result in an array
	 *
	 * @param $tables string The table or tables to query from
	 * @param $where string The clause to query by
	 * @param $args mixed Any args that should be used in the clause
	 * @param $_
	 * @return The query results
	 */
	public function fetchOne($tables, $where, $args = null, $_ = null) {
		$args = func_get_args();
		$result = call_user_func_array(array(&$this, 'fetch'), $args);

		if(count($result) > 1)
			throw new db_QueryFailedException('selectOne returned more than one result');

		return array_key_exists(0, $result) ? $result[0] : null;
	}
	
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
			'INSERT INTO %@ (%l) VALUES(%l)',
			$table,
			implode(', ', $names),
			$valueString
		);
		
		$this->queryReal ($query);
	}
		
	public function update ($args) {

		/* parse the query */
		$args = func_get_args ();
		$query = call_user_func_array (array ($this, 'parseUpdateQuery'), $args);

		/* perform query */
		$this->query ('%l', $query);
		
		return true;
	}
	
	public function updateOne ($args) {
		
		/* call update */
		$args = func_get_args ();
		$ret = call_user_func_array (array ($this, 'update'), $args);

		// ensure affected row count is correct
		if ($this->affected_rows () == 0)
			throw new sql_Exception ('No rows affected in updateOne()');
		if ($this->affected_rows () > 1) 
			throw new sql_Exception ('Multiple rows affected in updateOne()');

		return true;
	}
	
	public function parseUpdateQuery ($args) {

		$args = func_get_args ();
		$sets = $this->quotef_special ($args);
		$update = array_shift ($sets);
		$where = array_shift ($sets);
		if (count ($sets) == 0) $sets = array ($where);
	
		/* return update query string */
		return $this->format ('UPDATE %l SET %l WHERE %l',	
														$update, 
														implode (', ', $sets),
														$where
													);
	}
	
	
	
}
?>