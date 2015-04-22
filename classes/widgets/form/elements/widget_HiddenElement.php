<?php

class widget_HiddenElement extends widget_AbstractElement {

	protected $attributes		= array();
	
	public function __construct($args) {

		if (array_key_exists('attributes', $args) && is_array($args['attributes']))
			$this->attributes = $args['attributes'];
	}
	function renderElement() {

		$attributesHtml = '';
		if (count($this->attributes))
			foreach ($this->attributes as $key => $val)
				$attributesHtml.= sf(' %s="%s" ', $key, $val);
		
		return(sf("<input type='hidden' name='%s' value='%s' id='form_%s' class='inputHidden' %s />",
					$this->getName(),
					parent::makeInputSafe($this->getValue()),
					$this->getName(),
					$attributesHtml
				));
	}
}




?>