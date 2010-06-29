<?php
// Abstract data access model
abstract class mvc_AbstractDaoModel {
	
	static public function getAll($db) {
		
		dump(get_parent_class());
		
		
		$results  = $db->select($this->selectQuery);
		$returnArray = array();
		
		foreach( $results as $row ) {
			$forum = new gts_ForumAreaModel($db);
			$forum->loadFromSqlRow($row);
			$returnArray[] = $forum;		
		}
		return $returnArray;	
	}	

	abstract protected function getSelectQuery();
	
	final public function __construct($db) {
		
	}
	static public function load($db, $id) {
		if(!is_int($id)) throw new Exception("Identifier must be of type Integer");
		die(get_class());
		$file = new gts_ForumAreaModel($db);
		$file->loadFromId($id);
		return $file;
	}
	
	public function loadFromId($id) {
		
		$row  = $this->db->select_1("	
					SELECT f.id, f.path, f.user_id, f.size, f.memorial_id,  u.namefirst AS user_firstname, 
							u.namelast AS user_lastname, u.vc_urllocation AS user_url,
						f.order, mime_type, uploaded, caption
 					FROM file f
						INNER JOIN users u ON f.user_id = u.id
					WHERE f.id = %i ", $id);
		
		$this->loadFromSqlRow($row);

	}		
	
}

/*
 * ORM Implementation - put on ice
 *
class gts_ForumAreaModel extends orm_AbstractModel {
	 public function __construct() {
	 	
	 	// TODO : This is only here as PHP won't let this be declared
	 	// by default...
	 	
		$this->id 			= new orm_DataType_Integer(); 
		$this->name 		= new orm_DataType_Text();
		$this->sef_name 	= new orm_DataType_Text();
		$this->description 	= new orm_DataType_Text();
	
	
	

	 	$this->map("id", 			new orm_DataType_Integer()); 
	 	$this->map("name", 			new orm_DataType_Text()); 
	 	$this->map("sef_name", 		new orm_DataType_Text()); 
	 	$this->map("description", 	new orm_DataType_Text()); 
	 	
	 }
}
	*/
	
	

?>