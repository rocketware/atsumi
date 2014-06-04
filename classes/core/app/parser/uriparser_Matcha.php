<?php
/*
	Adds language localisation in URI's on top of Gyokuro

	Usage:

	Atsumi::app__setUriParser(
		new uriparser_Matcha(
			array(
				'en'=>array('gb'),
				'fr'=>array()
			)
		)
	);

	This would accept:

	 - en
	 - en-gb
	 - fr

	there is a locale array available in the meta
	data that contains language & country

*/

class uriparser_Matcha extends uriparser_Gyokuro {


	private $locale					= null;
	private $supportedLanguages 	= array();

	// you can optionally pass in the supported languages array
	// this will prevent matching none supported 2 letter pages
	public function __construct ($supportedLanguages = array()) {
		$this->supportedLanguages = $supportedLanguages;
		$this->locale = new mvc_LocaleModel();
	}


	public function parseUri($uri, $specification) {


		preg_match('@^/([a-z]{2})(-([a-z]{2})){0,1}/@', $uri, $localeMatch, PREG_OFFSET_CAPTURE);

		if (array_key_exists(0,$localeMatch) && (

				// check that it's in the allowed languages
				!count($this->supportedLanguages) ||
				in_array(
					$localeMatch[1][0],
					array_keys($this->supportedLanguages)
				)

		)) {

			$this->locale->set('language', $localeMatch[1][0]);

			if (array_key_exists(3,$localeMatch) && (

				// check it's an allowed country for current language
				!count($this->supportedLanguages) ||
				in_array(
					$localeMatch[3][0],
					$this->supportedLanguages[$this->locale->get('language')]
				)
			)) {
				$this->locale->set('country', $localeMatch[3][0]);
			}

			$uri = substr($uri, strlen($localeMatch[0][0])-1);
		}

		$output = parent::parseUri($uri, $specification);
		$output['meta']['locale'] = $this->locale;

		return $output;
	}
}
?>