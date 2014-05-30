<?php

/* generic caster for an web project */
class caster_Json extends caster_Abstract {

	protected $spec = array (
			's' => 'string',
			'n'	=> 'number',

			/* these aren't real JSON types, but can validate on them */
			'i'	=> 'integer',
			'f'	=> 'float',

			'b'	=> 'boolean',
			'a'	=> 'array',
			'o'	=> 'object',

			't'	=> 'timestamp'
		);

	/* annoyance due to PHP scope issue */
	static function cast ($args) {
		$parser =  new self();
		return  (string) $parser->castString(func_get_args());
	}

	static function cast_string ($in) {
		return sf("'%j'",$in);
	}

	static function cast_integer ($in) {
		return intval($in);
	}
	static function cast_float ($in) {
		return floatval($in);
	}
	static function cast_number ($in) {
		return ($in);
	}
	static function cast_boolean ($in) {
		return boolval($in);
	}

	static function cast_array ($in) {
		return json_encode($in);
	}
	static function cast_timestamp ($in) {
		return intval($in);
	}

	static function cast_object ($in) {
		return json_encode($in);
	}

}

?>