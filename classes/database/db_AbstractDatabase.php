<?php

/**
 * Used by all vender databases to gain basic functionality and forces
 * the implementation of the database interface.
 * @package Database
 */
abstract class db_AbstractDatabase implements db_InterfaceDatabase {

	/* The database connection object */
	protected $pdo;
	protected $connected = false;
	protected $queryTimes = array();
	protected $encrypter;
	protected $affectedRows;
	protected $debug = true;

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

		atsumi_Debugger::addDatabase($this);
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
	 * Return a database spersific, formatted string
	 *
	 * @param $format string The string to be formated
	 * @param $args mixed Format args
	 * @return string A string produced according to the formatting string format
	 */
	public function format($format, $args = null, $_ = null) {
		$args = func_get_args();
		$format = array_shift($args);

		if(count($args) <= 0)
			return $format;

		$ret = "";
		$pos0 = 0;
		$i = 0;
		while(true) {
			$pos1 = strpos($format, '%', $pos0);
			if($pos1 === false)
				return $ret . substr($format, $pos0);
			if($pos1 + 2 > strlen($format))
				throw new Exception('Invalid format string');
			$ret .= substr($format, $pos0, $pos1 - $pos0);
			$char = substr($format, $pos1 + 1, 1);

			if(!method_exists($this, $char))
				throw new Exception(sf('Unrecognised format char: %s(%s)', $ch, $format));

			$ret .= $this->$char($args[$i++],($char == 'a' ? false : true));
			$pos0 = $pos1 + 2;
		}
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
		$arg = func_get_args();
		return $this->queryReal(call_user_func_array(array(&$this, 'format'), $arg));
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

			$this->queryTimes[] = array(
				'sql'			=> $sql,
				'time'			=>(time() + microtime()) - $startTime,
				'row_count'		=> count($data)
			);

			return $data;
		} catch(PDOException $e) {
			throw new db_QueryFailedException($e->getMessage() . "<br />" . $sql);
		}
	}

	/* SELECT QUERIES */

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
}
?>