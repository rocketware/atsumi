<?php

class widget_FileElement extends widget_AbstractElement {

	private $fileToken = null;
	private $originalName = '';

	public function __construct($args) {

	}

	public function setValue($input = null, $files = null) {


		$uploadedFile = isset($files[$this->name]) ? $files[$this->name] : null;
		$fileRef = isset($input[$this->name]) ? $input[$this->name] : null;
		if($uploadedFile['error'] != 0 && $uploadedFile['error'] != 4 )
			throw new Exception("The uploaded file is too big");

		if(is_array($uploadedFile) && $uploadedFile['error'] == 0) {
			$this->fileToken = md5(microtime());
			copy($uploadedFile['tmp_name'], ini_get('upload_tmp_dir')."/".$this->fileToken);
			$uploadedFile['tmp_name'] = ini_get('upload_tmp_dir')."/".$this->fileToken;
			$this->originalName = $uploadedFile['name'];
			$_SESSION[$this->fileToken] = $uploadedFile;
			$this->value = $uploadedFile;
		} else if(is_string($fileRef)) {
			$parts = explode(',', $fileRef);
			$this->fileToken = $parts[1];
			$this->originalName = $parts[0];
			$this->value = $_SESSION[$this->fileToken];
		}

	}
    public function clear() {
        $this->fileToken = null;
        $this->originalName = '';
        $this->value = null;
    }

	protected function outputGeneric() {

		return array(	"name"			=> $this->getName(),
						"value"			=> !($this->validates) ? null : $this->originalName.",".$this->fileToken,
						"validation"	=> $this->getValidationData()
					);
	}
	function renderElement() {

		if(is_array($this->value)) {
			$parts = array($this->originalName, $this->fileToken);
		} else {
			$parts = explode(",",$this->getValue());
	}

		if($this->validates && count($parts)>1)
		return(sf("<div class='inputFileDone'>file uploaded: <strong>%s</strong></div><input type='hidden' name='%s' value='%s' id='form_%s' />",
							$parts[0],
							$this->getName(),
							$this->originalName.",".$this->fileToken,
							$this->getName()
				));
		else
		return(sf("<input type='file' name='%s' %s id='form_%s' class='inputFile' />",
							$this->getName(),
							($this->tabindex) ? sf('tabindex="%s"', $this->tabindex) : '',
							$this->getName()
				));
	}


}

?>