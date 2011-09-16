<?php

class db_PostgreSQLRow extends dp_AbstractRow {
	
	protected function initCaster() {
		$this->caster = new caster_PostgreSQLToPhp();
	}
	
}

?>

