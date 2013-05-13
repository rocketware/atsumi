<?php
abstract class mvc_AbstractModel {
	
	/* generic */
	private $data = array();
	
	
	
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
	
		array_unshift($args, static::DB_TABLE_NAME);
	
		$result = call_user_func_array(array($db, $method), $args);
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
		foreach ($rowData as $k => $v)
			$this->data[$k] = caster_PostgreSqlToPhp::cast(sf('%%%s', $this->structure[$k]['type']), $v);
	}
	

	/* generic */
	function has ($k) {
		 return array_key_exists($k, $this->data);
	}

	/* generic */
	function set ($k, $v) {
		$this->data[$k] = $v;
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