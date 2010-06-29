<?php

class widget_TextWithSuggestionsElement extends widget_AbstractElement {
	private $options = array();
	private $beforeText = "";
	private $afterText = "";

	public function __construct($args) {
		$this->options 		= $args['options'];
		$this->beforeText 	= $args['beforeText'];
		$this->afterText 	= $args['afterText'];
	}
	public function setValue($input) {
		if($input[$this->name] == 'custom' && array_key_exists(sf('%s_custom',$this->name), $input)) $this->value = $input[sf('%s_custom',$this->name)];
		else $this->value =  $input[$this->name];
	}
	function renderElement() {
		$html = "";
		$elementValue = $this->getValue();
		$usedSuggested = false;
		
		foreach($this->options as $value => $option) {
			if(strval($elementValue) == strval($value)) {
				$usedSuggested = true;
				$html .= sfl('<div class="radioOption"><input type="radio" name="%s" value="%s" checked="checked" /> %s</div>', 
									$this->getName(), 
									parent::makeInputSafe($value),
									$option
				);
			} else
				$html .= sfl('<div class="radioOption"><input type="radio" name="%s" value="%s" /> %s</div>', 
									$this->getName(), 
									parent::makeInputSafe($value), 
									$option
				);
		}
		
		return sf("<div class='textWithSuggestions'>%s
						<div class='radioOption'>%s %s<input type='text' name='%s' value='%s' />%s</div></div>",
								$html, 
								sfl('<input type="radio" name="%s" value="custom" %s />', $this->getName(), $usedSuggested ? "" :'checked="checked"'),
								 $this->beforeText,
								 $this->getName()."_custom",
								 $usedSuggested ? "" : parent::makeInputSafe($this->getValue()), 
								 $this->afterText
							);
	}
}

?>