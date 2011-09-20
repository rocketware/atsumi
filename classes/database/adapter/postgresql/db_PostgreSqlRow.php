<?php

class db_PostgreSqlRow extends dp_AbstractRow {
	
	protected function initCaster() {
		if (is_null($this->caster))
			$this->caster = new caster_PostgreSqlToPhp();
	}
	
}

?>

