<?php
class caster_PostgreSQL extends caster_Abstract {
	
	protected $spec = array(
		'@' => 'tableName',
		'a' => 'sqlArray',
		'b' => 'boolean',
		'c' => 'character',
		'C' => 'characterVarying',
		'd' => 'date',
		'f' => 'float',
		'i' => 'integer',
		'l' => 'literal',
		'n' => 'numeric',
		'q' => 'fullTextQuery',
		's' => 'text',
		'S' => 'textOrNull',
		't' => 'timestampWithTimezone',
		'v' => 'fullTextVector',
		'x' => 'binary',
		);
		
	/* annoyance due to PHP scope issue */
	static function cast ($args) {
		$parser =  new self();
		return  (string) $parser->castString(func_get_args());
	}
		
	
	/* casters */
	static function tableName ($in) {
		return sf('"%s"', $in);	
	}

	static function text ($in) {
		if (!is_string($in)) throw new parser_StrictTypeException ('Expected String');
		return sf("'%s'::TEXT", pg_escape_string($in));	
	}
	
	static function textOrNull ($in) {
		if (!is_string($in) && !is_null($in)) throw new parser_StrictTypeException ('Expected String or Null');
		
		if (is_string($in) && strlen($in)) return self::text($in);
		elseif (is_null($in)) return 'NULL';
	}
	
	static function sqlArray ($in) {
		
		$sqlArr = "";
		foreach($in as $item) {
			$sqlArr .= ($sqlArr == "") ? "" : ", ";
			$sqlArr .= is_int($item) ? $item : "'".$item."'";
		}
			
		return "ARRAY[".$sqlArr."]";
	}
	
	static function character ($in) {
		return sf("'%s'::CHARACTER", pg_escape_string($in));	
	}
	
	static function characterVarying ($in) {
		return sf("'%s'::CHARACTER VARYING", pg_escape_string($in));	
	}
	
	static function float ($in) {
		return sf("'%s'::DOUBLE PRECISION", $in);	
	}
	
	static function boolean ($in) {
		return sf("%s::BOOLEAN", $in?'t':'f');	
	}
	
	static function integer ($in) {
		return sf("%s::INTEGER", $in);	
	}
	
	static function numeric ($in) {
		return sf("%s::NUMERIC", $in);	
	}
	
	static function binary ($in) {
		return sf("'%s'::BYTEA", pg_escape_bytea($in));	
	}
	
	static function timestampWithTimezone ($in) {
		return sf("'%s'::TIMESTAMP WITH TIMEZONE", pg_escape_string (gmdate ('Y-m-d H:i:s+00', $in)));	
	}
	
	static function date ($in) {
		return sf("'%s'::DATE", $in);	
	}
	
	static function literal ($in) {
		return $in;	
	}
	
	static function fullTextQuery ($in) {
		return sf("'%s'::TSQUERY", $in);	
	}
	
	static function fullTextVector ($in) {
		return sf("'%s'::TSVECTOR", $in);	
	}
	
}
?>