<?php
/**
 * File defines all functionaility of the db_MySql class.
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * MySql adaptor class
 * @package		Database
 * @subpackage	Vender.MySql
 * @author		Chris Chrisostomou
 * @since		1.0
 */

class db_MySql extends db_AbstractDatabase {
	
	public function connect($config = array()) {
		// Build the vender connection string
		$conString = sf(
				'mysql:%s;%s',
				(isset($config['host'])		? sf('host=%s',		$config['host']) : ''),
				(isset($config['dbname'])	? sf('dbname=%s',	$config['dbname']) : '')
		);
	
		// Call the base connection function
		$this->connectReal($conString, $config);
	}
	
	protected function createRow ($rowData) {
		return new db_MySqlRow($rowData);
	}
	
	/**
	 * Initalise the vender type caster
	 */
	protected function initCaster() {
		$this->caster = new caster_MySql();
	}
	
	/**
	 * Returns true if the database supports transaction
	 * @return boolean If the database supports transactions
	 */
	public function transactionSupport() {
		// MySam does't support transactions and will just ignore begin, commit, rollback, etc
		// Innodb will work as expected
		return true;
	}
	
	/**
	 * Returns true if the database is in transaction
	 * @return boolean If the database is in transaction
	 */
	public function inTransaction() {
		return $this->transaction;
	}
	
	/**
	 * Begins a database transaction. Will fail if there is already a transaction in progress.
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
	 * Commits a database transaction. Will fail if there is no transaction in progress.
	 * @return boolean If commiting the transaction was successful
	 */
	public function transactionCommit() {
 		if(!$this->transaction)
 			throw new db_Exception('Cannot call commitTransaction() while not in transaction');
	
 		$this->transaction = false;
 		return $this->query('COMMIT');
	}
	
	/**
	 * Rolls back a database transaction. Will fail if there is no transaction in progress.
	 * @return boolean If rolling back the transaction was successful
	 */
	public function transactionRollback() {
 		if(!$this->transaction)
 			throw new db_Exception('Cannot call rollbackTransaction() While not in transaction');
	
		$this->transaction = false;
 		return $this->query('ROLLBACK');
	}
	
	/**
	 * Rolls back a database transaction, if there is one in progress. This is designed for use in
	 * catch blocks to ensure a rollback in case of error.
	 */
	public function transactionAutoRollback() {
 		if($this->transaction)
 			$this->transactionRollback();
	}
	
	public function nextval ($name) {
		//TODO: return last id + 1?
		return false;
	}
}
?>