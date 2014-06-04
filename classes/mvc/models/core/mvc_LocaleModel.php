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

}

?>