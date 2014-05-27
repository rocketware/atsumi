<?php

/* generic caster for an web project */
class caster_Php extends caster_Abstract {

	protected $spec = array (
			's' => 'string',
			'n'	=> 'number',

			/* these aren't real JSON types, but can validate on them */
			'i'	=> 'integer',
			'f'	=> 'float',

			'b'	=> 'boolean',

			'a'	=> 'array',
			'o'	=> 'object'
		);


	/* returns a value */
	static function value ($format, $value) {

		$parser = new self();

		$methodName = 'cast_'.$parser->spec[$format];
		return $parser->$methodName($value);

	}

	static function cast_string ($in) {
		return strval($in);
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
		return $in;
	}

	static function cast_object ($in) {
		return $in;
	}

}

?>