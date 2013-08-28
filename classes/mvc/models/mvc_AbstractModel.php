<?php
abstract class mvc_AbstractModel {
	
	/* generic */
	protected $data = array();
	
	
	
	/* generic */
	public function __construct () {
		foreach ($this->structure as $k => $properties) {
			$this->set(
				$k, 
				isset($properties['default'])?
					$properties['default']:null
			);
		}
	}
	
	static public function write ($db, $o) {
		
			
		$method = !is_null($o->get('id', false))?'update':'insert';

		$args = array();
		
		if ($method == 'update') {
			$args[] = sf('%s = %s', 'id', $o->get('id'));
		}

//		$vars = $this->getChanges();
		foreach($o->data as $key => $value) {
			if (!$o->structure[$key]['write']) continue;
			$args[] = sf('%s = %%%s', $key, $o->structure[$key]['type']);
			$args[] = $value;
		}
	
		if (!count($args))
			throw new Exception ('Model has nothing to write...');
		
		array_unshift($args, static::DB_TABLE_NAME);
	
		$result = call_user_func_array(array($db, $method), $args);
				
		if (is_null($o->get('id', false))) {
			$id = $db->selectOne('select lastval()');
			$o->set('id', $id->i_lastval);
		}
		
	}
	
	/* generic */
	static protected function load ($db, $where = null, $orderBy = null, $offset = null, $limit = null) {
		
		if (is_null($where)) $where = '';
		else $where = ' where '.$where;
		
		if (is_null($orderBy)) $orderBy = '';
		else $orderBy = ' order by '.$orderBy;
		
		if (is_null($offset)) $offset = '';
		else $offset = ' offset '.$offset;
		
		if (is_null($limit)) $limit = '';
		else $limit = ' limit '.$limit;
		
		$rows = $db->select('%l %l %l %l %l', 
				static::DB_SELECT_QUERY, $where, $orderBy, $offset, $limit);
		
		$arrOut = array();
		
		if (!$rows) return $arrOut;
		
		foreach ($rows as $row) {
			$obj = new static();
			$obj->populateFromSqlRow($row);
			$arrOut[] = $obj;
		}
		
		return $arrOut;
		
	}

	/* generic */
	function populateFromSqlRow ($r) {
 		$rowData = $r->getData();	
		foreach ($rowData as $k => $v) {
			if (!array_key_exists($k, $this->structure))
				throw new Exception (sf('Unexpected row column "%s", add this to the models \'structure\' member variable.', $k));
			
			$this->data[$k] = caster_PostgreSqlToPhp::cast(sf('%%%s', $this->structure[$k]['type']), $v);
		}
	}
	

	/* generic */
	function has ($k) {
		 return array_key_exists($k, $this->data);
	}

	/* generic */
	function set ($k, $v) {
		$this->data[$k] = $v;
	}

	function increment ($k, $v) {
		if (!$this->has($k)) throw new Exception('unknown key: '.$k);	
		if (!is_numeric($this->data[$k])) throw new Exception('Key: '.$k.' is not numeric, can not increment');	
		$this->data[$k] += $v;
	}
	
	/* generic */
	function get($key, $strict = true) { 
		if (!array_key_exists($key, $this->data))
			throw new Exception ('Unknown model key: '. $key);
		
		return $this->data[$key];
		/*
		try {
			$value = caster_PostgreSqlToPhp::cast(sf('%%%s', $this->structure[$key]['type']), $this->data[$key]);
			return $value;
		} catch (Exception $e) {
			if ($strict) throw $e;
			return null;
		}
		*/
	}
}
?>