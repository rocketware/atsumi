<?php

class db_MySqlRow extends db_AbstractRow {
	
	protected function initCaster() {
		if (is_null($this->caster))
			$this->caster = new caster_MySqlToPhp();
	}
	
}

?>