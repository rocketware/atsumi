<?php
/*
 * suggested styles:

	div.inputImagePicker { padding:10px; }
	div.inputImagePicker label.imgLabel { padding:0; margin:0;  cursor:pointer; width:auto; }
	div.inputImagePicker .imgContainer { float:left;  text-align:center;  padding-bottom:10px; }
	div.inputImagePicker .imgContainer input { clear:both; }

 */

class widget_ImagePickerElement extends widget_AbstractElement {
	private $options = array();
	private $blankMessage = "Please Select";

	private $width = 100;
	private $height = 100;
	
	
	public function __construct($args) {
		$this->options = $args['options'];
		if(array_key_exists('blank', $args))
			$this->blankMessage = $args['blank'];
		
		if(array_key_exists('width', $args))
			$this->width = $args['width'];
		
		if(array_key_exists('height', $args))
			$this->height = $args['height'];
	}
	function renderThumbnail ($value, $imageUrl, $selected) {
		
		// TODO: 	Would be nice to add option for multi select
		//			would convert to checkbox's rather than radio's
		
		return sfl('<div style="width:%spx;" class="imgContainer">
						<label for="form_%s[%s]" class="imgLabel">
							<img src="%s" style="width:%spx; height:%spx;" />
						</label>
						<input type="radio" id="form_%s[%s]" name="%s" value="%s" %s />
					</div>',
					$this->width,
					$this->getName(),
					$value,
					$imageUrl,
					$this->width,
					$this->height,
					$this->getName(),
					$value,
					$this->getName(),
					$value,
					$selected?'checked="checked"':'');
		
	}
	function renderElement() {
		$htmlOut = "";
		$elementValue = $this->getValue();

		foreach($this->options as $value => $imageUrl) {
			
			$htmlOut .= $this->renderThumbnail(
							$value,
							$imageUrl,
							strval($elementValue) == strval($value)
						);
			
		}

		return(	sfl("<div name='%s' %s id='form_%s' class='inputImagePicker'>%s</div>",
						$this->getName(),
						($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '',
						$this->getName(),
						$htmlOut

					)
				);
	}
}

?>