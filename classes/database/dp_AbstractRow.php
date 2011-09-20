<?php
atsumi_Loader::references(array(
						'atsumi' 			=> 'utility/calendar'
					));

abstract class dp_AbstractRow {
	
	private 	$rowData;
	protected 	$caster;
	
	abstract protected function initCaster ();
	
	public function __construct ($rowData) {
		$this->rowData = $rowData;
	}
	public function cast ($format, $column) {
		
		if (!array_key_exists($column, $this->rowData))
			throw new db_RowColumnNotFoundException('Column not found: '.$column);
		
		$data = $this->rowData[$column];
		$this->initCaster();
		
		return $this->caster->cast('%'.$format, $data);
		
	}
	public function getRaw ($column) {
		return $data[$column];
	}
}

?>