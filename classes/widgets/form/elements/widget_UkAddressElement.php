<?php

class widget_UkAddressElement extends widget_AbstractElement {

	public function __construct($args) {

	}

	protected function preRender() { return "<div class='address'>"; }

	protected function postRender() { return "</div>"; }

	function renderElement() {

		$address = $this->getValue();
		if(!is_array($address)) $address = array();
		if(!array_key_exists('address1',$address)) $address['address1'] = null;
		if(!array_key_exists('address2',$address)) $address['address2'] = null;
		if(!array_key_exists('postcode',$address)) $address['postcode'] = null;
		if(!array_key_exists('town',$address)) $address['town'] = null;

		/*
		 * The rendering of this element is SO messy due to IE compatibility - needs rewriting
		 *
		 */
		$out = sf("<div style='float:left;'><input type='text' name='%s[address1]' value='%s' %s id='form_%s' class='inputUkAddress inputUkAddress1' /><br />",
							$this->getName(), parent::makeInputSafe($address['address1']), ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', $this->getName()
				);

		$out .= sf("<input type='text' name='%s[address2]' value='%s' %s id='form_%s' class='text' class='inputUkAddress inputUkAddress2' /><br />",
							$this->getName(), parent::makeInputSafe($address['address2']), ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', $this->getName()
				);

		$out .= sf("</div><div style='clear:both;'><label for='form_%s'>%s</label>",
					$this->getName(), "Post Code"
				);

		$out .=	sfl("<table style='border:0px; border-spacing:0px;.' cellpadding=0 cellspacing=0><tr><td style=''><input type='text' name='%s[postcode]' value='%s' %s id='form_%s' class='postCode'  class='inputUkAddress inputUkAddressPostCode'  />",
						$this->getName(), parent::makeInputSafe($address['postcode']), ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', $this->getName()
				);

		$out .=	sfl("</td><td class='tRight'><label class='town'>Town</label></td><td><input type='text' name='%s[town]' value='%s' %s id='form_%s' class='town'  class='inputUkAddress inputUkAddressTown' /></td></tr></table></div>",
						$this->getName(), parent::makeInputSafe($address['town']), ($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '', $this->getName()
				);

		return $out;

	}
}

?>