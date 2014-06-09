<?php

class mvc_LocaleModel extends mvc_AbstractModel {


	protected $structure = array(
		'language'			=> array(
			'type'		=> 's',
		),
		'country'		=> array(
			'type'		=> 's',
		)
	);

	function getLocaleString () {
		return sf('%s%s',
			strtolower($this->get('language')),
			$this->has('country')?
				sf('_%s', strtoupper($this->has('country'))):''
		);
	}

	function uri ($uri) { return sf('/%s%s%s',
		$this->getUriPrefix(),
		substr($uri, 0, 1) !== '/'?'/':'',
		$uri
	);
	}
	function getUriPrefix () {
		return sf('%s%s',
			strtolower($this->get('language')),
			$this->has('country')?
				sf('-%s', strtolower($this->has('country'))):''
		);
	}

	static function parseHttpAcceptLanguage ($httpAcceptLanguage) {

		$langs = array();

		// break up string into pieces (languages and q factors)
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
			$httpAcceptLanguage,
			$langMatches
		);

		if (count($langMatches[1])) {
			// create a list like "en" => 0.8
			$langs = array_combine($langMatches[1], $langMatches[4]);

			// set default to 1 for any without q factor
			foreach ($langs as $lang => $val) {
				if ($val === '') $langs[$lang] = 1;
			}

			// sort list based on value
			arsort($langs, SORT_NUMERIC);
			$langs = array_change_key_case($langs, CASE_LOWER);
		}

		return $langs;
	}

}

?>