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


	protected $transaction;
	

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
	
	
	public function formatResult ($rows) {
	
		$rowArr = array();
		foreach ($rows as $idx => $row)
			$rowArr[] = $this->createRow($row);
	
		return $rowArr;	
	}
	
	

	/**
	 * Executes a basic query with casted variables
	 * @param string $query The query string
	 * @param mixed $args The args to be casted into the query string
	 * @param mixed $_ Arg repeater (Repeat the last arg as many times as needed)
	 */
	public function query($query, $args = null, $_ = null) {
		$args = func_get_args();
		return $this->formatResult($this->queryReal(call_user_func_array(array(&$this, 'cast'), $args)));
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

			$data = $return->fetchAll(PDO::FETCH_ASSOC);
			
			$this->affectedRows = count($data);
			
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
			throw new db_QueryFailedException($e->getMessage() . "<h4>SQL:</h4>" . $sql);
		}
	}



	/* 
	 * SELECT 
	 * */
	public function select($args) {
		$args = func_get_args();
		return call_user_func_array(array(&$this, 'query'), $args);
	}
	public function selectOne($args) {
		$args = func_get_args();
		$result = call_user_func_array(array(&$this, 'select'), $args);

		if(count($result) > 1)
			throw new db_UnexpectedResultException('selectOne returned more than one result');

		return array_key_exists(0, $result) ? $result[0] : null;
	}




	/* FETCH
	 * abstract select - for simple queries (no joins) */
	public function fetch ($cols, $table, $where = null, $orderBy = null, $offset = null, $limit = null) {
		/* parse the query */
		$args = func_get_args();
		$query = call_user_func_array (array ($this, 'parseFetchQuery'), $args);

		/* perform query */
		$result = $this->query('%l', $query);

		return $result;
	}
	public function fetchOne($cols, $table, $where = null) {
		$args = func_get_args();
		$result = call_user_func_array(array(&$this, 'fetch'), $args);

		if(count($result) > 1)
			throw new db_UnexpectedResultException('fetchOne returned more than one result');
		return array_key_exists(0, $result) ? $result[0] : null;
	}
	public function parseFetchQuery($cols, $table, $where = null, $orderBy = null, $offset = null, $limit = null) {
		
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');

		// strip out the nulls
		$argsReal = func_get_args ();
		$args = array();
		foreach ($argsReal as $arg)
			$args[] = !is_null($arg)?$arg:'';
		$cols = array_shift ($args);
		$table = array_shift ($args);
		
		// handle offset limit
		$offset = null;
		$limit 	= null;
		
		if (is_int(end($args))) $limit 	= array_pop($args);
		if (is_int(end($args))) $offset = array_pop($args);
		
		if (count($args) && $args[0] !== null) {
			$sets = $this->caster->castArraySets($args);
			$where = array_shift ($sets);
		}	
		if (count($args) && $args[0] !== null) {
			$orderBy = array_shift ($sets);
		}	
		// return select query string
		return $this->caster->castString(
			'SELECT %l FROM %@%l%l%l%l', $cols, $table, 
			empty($where)?'':$this->caster->castString(' WHERE %l', 		$where),
			empty($orderBy)?'':$this->caster->castString(' ORDER BY %l', 	$orderBy),
			!is_int($offset)?'':$this->caster->castString(' OFFSET %i', 	$offset),
			!is_int($limit)?'':$this->caster->castString(' LIMIT %i', 		$limit)
			
		);
	}

	
	/**
	 * INSERT
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
		/* parse the query */
		$args = func_get_args();
		$query = call_user_func_array (array ($this, 'parseInsertQuery'), $args);

		/* perform query */
		$this->query('%l', $query);

		return true;
	}
	public function insertOrUpdateOne ($table, $where, $values) {
		
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');
		
		$args = func_get_args();
		$sets = $this->caster->castArraySets($args);
		$table = $sets [0];
		$where = $sets [1];

		$exists = $this->exists ($table, '%l', $where);
		
		call_user_func_array (array ($this, $exists? 'updateOne' : 'insert'), $args);
		
	}
	public function deleteAndInsert ($args) {
		throw new Expcetion ('TODO');	
	}	
	public function parseInsertQuery($args) {
		
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');
		
		$args = func_get_args();
		$table = array_shift($args);
		
		
		$rows = $this->caster->castArraySets($args);
		$column = array();
		$data 	= array();

		foreach($rows as $row) {
			$rowParts 	= explode('=', $row);
			$column[] 	= trim($rowParts[0]);
			$data[] 	= trim($rowParts[1]);
		}

		return $this->caster->castString(
			'INSERT INTO %@ (%l) VALUES(%l)', $table, implode(', ', $column), implode(', ', $data)
		);
	}

	/*
	 * UPDATE
	 * 
	 * */
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
		if($this->getAffectedRows () == 0)
			throw new db_UnexpectedResultException ('No rows affected in updateOne()');
		if($this->getAffectedRows () > 1)
			throw new db_UnexpectedResultException ('Multiple rows affected in updateOne()');

		return true;
	}
	public function parseUpdateQuery($args) {
		
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');

		$args = func_get_args ();
		$sets = $this->caster->castArraySets($args);
		$update = array_shift ($sets);
		$where = array_shift ($sets);
		if (count ($sets) == 0) $sets = array ($where);
		
		/* return update query string */
		return $this->caster->castString(
			'UPDATE %@ SET %l WHERE %l', $update, implode(', ', $sets), $where
		);
	}

	/*
	 * EXISTS
	 * */
	public function exists ($table, $where) {
		
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');
		
		$args = func_get_args();
		$table = array_shift($args);
		$where = $this->caster->castArraySets($args);
		
		$result = $this->query(
			'SELECT CASE WHEN EXISTS (
				SELECT *
					FROM %@
					WHERE %l
			) THEN %b ELSE %b END AS exists', 
			$table, $where[0], true, false
		);
		
		$exists = $result[0];
		return $exists->cast('b', 'exists');
	}	

	/*
	 * COUNT
	 * */
	public function count ($table, $where = null) {
		
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');
		
		$args = func_get_args();
		$table = array_shift($args);
		
		$where = (count($args) && !is_null($args[0]))?$this->caster->castArraySets($args):array('1=1');
		
		$result = $this->query(
			'SELECT COUNT(*) AS count FROM %@ WHERE %l', 
			$table, $where[0]
		);
		
		$exists = $result[0];
		return $exists->cast('i', 'count');
	}	



	/*
	 * DELETE
	 * */
	public function delete ($args) {
		/* parse the query */
		$args = func_get_args();
		$query = call_user_func_array (array ($this, 'parseDeleteQuery'), $args);

		/* perform query */
		$this->query('%l', $query);

		return true;
	}	
	public function deleteOne ($args) {
		throw new db_Exception ('TODO');	
	}	
	public function parseDeleteQuery ($args) {
		if (is_null($this->caster))
			throw new db_Exception('Caster not loaded.');

		$args = func_get_args ();
		$sets = $this->caster->castArraySets($args);
		$deleteFrom = array_shift ($sets);
		$where = array_shift ($sets);
		
		/* return update query string */
		return $this->caster->castString(
			'DELETE FROM %@ WHERE %l', $deleteFrom, $where
		);

	}
	
	/*
	 * Table management
	 * */
	public function createTable ($args) {
		throw new db_Exception ('TODO');	
	}	
	public function dropTable ($args) {
		throw new db_Exception ('TODO');	
	}	

}
?>