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
					$properties['default']:null,
				true
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
			$o->set('id', $id->i_lastval, true);
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
 		
 		// itterate through the db columns and populate the object
		foreach ($rowData as $k => $v) {
			
			// check we're expecting the currently column
			if (!array_key_exists($k, $this->structure))
				throw new Exception (sf('Unexpected row column "%s", add this to the models \'structure\' member variable.', $k));
			
			try {
				
				$this->data[$k] = caster_PostgreSqlToPhp::cast(sf('%%%s', $this->structure[$k]['type']), $v);
			
			// if casting error throw a useful exception
			} catch (Exception $e) {
				$spec = caster_PostgreSqlToPhp::getSpec();

				if (!array_key_exists($this->structure[$k]['type'], $spec))
					throw new caster_Exception (
						sf("Caster doesn't support format: %%%s",
							$this->structure[$k]['type']
						)
					);

				throw new caster_Exception (
					sf("%s: '%s' could not be cast as '%s' (%%%s)",
						$k,
						is_null($v)?'NULL':$v,
						$spec[$this->structure[$k]['type']],
						$this->structure[$k]['type']
					)
				);
			}
		}
	}
	

	/* generic */
	function has ($k) {
		 return array_key_exists($k, $this->data);
	}

	/* generic */
	function set ($k, $v, $force = false) {
		if (!$force && !$this->structure[$k]['write'])
			throw new Exception ('Column not writable');

		$this->data[$k] = $v;
	}

	function increment ($k, $v) {
		if (!$this->has($k)) throw new Exception('unknown key: '.$k);

		// increment different data types
		switch ($this->structure[$k]['type']) {

			// INTERVAL
			case 'z':
			case 'Z':

				// check if it's null
				if (is_null($this->data[$k]))
					$this->data[$k] = new atsumi_Interval(0);

				$this->data[$k]->add(new atsumi_Interval($v));
				break;

			// NUMERIC
			case 'i':
			case 'I':
			case 'e':
			case 'E':
			case 'n':
			case 'N':
			case 'f':
			case 'F':

				// check if it's null
				if (is_null($this->data[$k]))
					$this->data[$k] = 0;

				$this->data[$k] += $v;
				break;


			default:
				throw new Exception("Unexpected data type to increment %".$this->structure[$k]['type']);

		}
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