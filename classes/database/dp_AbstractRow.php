<?php

abstract class dp_AbstractRow {
	
	private 	$data;
	protected 	$caster;
	
	abstract protected function initCaster ();
	
	public function __construct ($rowData) {
		$this->data = $rowData;
	}
	
	public function cast ($format, $column) {
		
		if (!array_key_exists($column, $this->rowData))
			throw new db_RowColumnNotFoundException('Column not found: '.$column);
		
		$data = $this->date[$column];
		
		$caster = $this->initCaster();
		
		return $caster->cast('%'.$format, $data);
		
		
	}
	
	
}

?>