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
				sf('-%s', strtolower($this->get('country'))):''
		);
	}

	function uri ($uri) {
		return sf('/%s%s%s',
			$this->getLocaleString(),
			substr($uri, 0, 1) !== '/'?'/':'',
			$uri
		);
	}
	static public function fromLocaleString ($localeString) {

		$locale = new self();
		preg_match('/([a-z]{2})(-([a-zA-Z]{2}))*/i',
			$localeString,
			$matches
		);
		if (array_key_exists(1, $matches))
			$locale->set('language', strtolower($matches[1]));

		if (array_key_exists(3, $matches))
			$locale->set('country', strtolower($matches[3]));

		return $locale;

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
	static function interpolate ($str, $vars) {
		$originalStr = $str;
		while (preg_match('/\$\{(.*?)\}/sm', $str, $m)) {
			list($src, $var) = $m;

			// retrieve variable to interpolate in context, throw an exception
			// if not found.
			
			if (!array_key_exists($var, $vars)) {
				
				// TODO: Add a custom exception
				Atsumi::error__listen(
					new Exception (
						sf('Locale interpolation failed in for var: "%s" in str: "%s"',
							$var, $originalStr
						)
					)
				);
				$value = '';
			} else $value = $vars[$var];
			$str = str_replace($src, $value, $str);
		}
		
		foreach ($vars as $key => $replacement) {
			$str = str_replace ('${'.$key.'}', $replacement, $str);
		}
		return $str;		
		
	}

}

?>