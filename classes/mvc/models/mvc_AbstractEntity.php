<?php

/*
 * AbstractEntity : This class is the base class for any model that can be loaded
 * from a database. The class processes its childs database a generates Sql that
 * when used in conjunction with the database abstraction layor removes any need
 * for a programmer to write repeative code.
 *
 * How it works:
 *
 * The child model can contain any number of manipulation functions that change the
 * variables within the class. All changes are noted and when the class is called to
 * write they are pushed to the database.
 *
 * Variables:
 * Variable should start with a type identifier using the same sintax used by the sf
 * function set, followed by an underscore. This strictly types the variable for
 * database pushing but does not affect calls to the variable.
 *
 * Example:
 *
 *  class project_Example extends mvc_AbstractEntity {
 *   public i_id = 0;
 *
 *   public function Test() {
 *	$this->id = 2;
 *   }
 *  }
 *
 *  $instance = new project_Example;
 *  $instance->id = 3;
 *
 * Variable named id of strict type integer, called as id from with and outside the
 * class.
 */
class mvc_AbstractEntity extends atsumi_DebugModel {

	/* string Name of the table the model belongs to */
	protected $table = 'default';

	/* Values indiviual to each object, e.g. sid */
	protected $pkeys = array();

	/* Array to hold parsed variable data */
	private $vars = array();

	/* List of all keys */
	private $keys = array();

	/**
	 * Parses the childs variable data
	 *
	 * @todo Add scope processing
	 */
	public function __construct($db = null, $id = null) {
		$child = get_class_vars(get_class($this));
		$parent = get_class_vars('mvc_AbstractEntity');

		$diff = array_diff_key($child, $parent);

		foreach($diff as $key => $value) {
			$set = explode('_', $key, 2);

			if(count($set) < 2 && strlen($set[0]) != 1)
				continue;

			$data = array(
				'name'		=> $key,
				'type'		=> $set[0],
				'default'	=> $value,
				'value'		=> $value,
				'changed'	=> false
			);

			$this->keys[] = $set[1];
			$this->vars[$set[1]] = $data;
		}

		if(!is_null($id)) {
			$this->id = $id;
			$this->read($db, 'id = %i', $this->id);
		}
	}

	public function getChanges() {
		$changedVars = array();
		foreach($this->vars as $key => $var) {
			if($var['changed'])
				$changedVars[$key] = $var;
		}
		return $changedVars;
	}

	/*
	 * Override of the default get method so that the
	 * value in the vars array is changed instead of
	 * the actual variable
	 */
	protected function __get($id) {
		if(array_key_exists($id, $this->vars))
			return $this->vars[$id]['value'];

		return null;
	}

	/*
	 * Override of the default set method so that the
	 * value in the vars array is retruned instead of
	 * the actual variable
	 */
	protected function __set($id, $value) {
		if(!array_key_exists($id, $this->vars))
			throw new mvc_UndefinedClassVariableException();

		// TODO: Do variable check, throw on type conflict

		if($this->vars[$id]['value'] != $value)
			$this->vars[$id]['changed'] = true;

		$name = $this->vars[$id]['name'];

		$this->$name = $value;
		$this->vars[$id]['value'] = $value;
	}

	protected function __unset($id) {
		$this->__set($id, null);
	}

	/**
	 * Used to load a single model instance by
	 * something other than a primary key
	 */
	public function read($db, $where_etc = null) {
		$args = func_get_args();

		$db = array_shift($args);

		if(count($args) == 0) {
			$vars = $this->getChanges();
			$args = array('');
			$clause = array();
			foreach($vars as $key => $var) {
				$clause[] = sf('%s = %%%s', $key, $var['type']);
				$args[] = $var['value'];
			}
			$args[0] = implode(' AND ', $clause);
		}

		array_unshift($args, $this->table);
		array_unshift($args, $this->keys);

		$result = call_user_func_array(array(&$db, 'select'), $args);

		if($result === null)
			throw new Exception('Entity not found');

		dump($result);
		die();
	}

	/**
	 * Used to write the contents of the class to
	 * the database it was linked with on creation
	 */
	public function write($db, $where_etc = null) {
		$args = func_get_args();

		$method = 'update';

		$db = array_shift($args);
		if(count($args) == 0) {
			// If any of the pkey's is still what is was on creation then no
			// loading has taken place and the object must be created
			foreach($this->pkeys as $pkey) {
				if($this->vars[$pkey]['value'] == $this->vars[$pkey]['default']) {
					$method = 'insert';
					break;
				}
			}
		}

		$vars = $this->getChanges();
		$args = array();
		$values = array();
		foreach($vars as $key => $var) {
			$args[] = sf('%s = %%%s', $key, $var['type']);
			$args[] = $var['value'];
		}

		array_unshift($args, $this->table);

		$result = call_user_func_array(array(&$db, $method), $args);
	}

	/**
	 * Used to remove the instance from the database
	 * this can be used in conjunction with the write
	 * command to create a new instance of the model
	 */
	public function remove() {
		//echo 'Removing from database. Table: '. $this->table;
	}

	/**
	 * Used to set all variable changes to false, this
	 * prevents changes made being writen to the database
	 */
	public function clearChanges() {
		foreach($this->vars as &$value) {
			$value['changed'] = false;
		}
	}
}
?>