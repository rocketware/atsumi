<?php
class mvc_BinaryView extends mvc_AbstractView {

	public function setHeaders() {
		
		//file type
		header(sf("Content-type: %s", $this->get_mime_type));
		
		//content length
		if(!is_null($this->get_file_size))
			header(sf("Content-Length: %s", $this->get_file_size));
		
		if(!is_null($this->get_expires))
			header(sf("Expires: %s", $this->get_expires));
			
		if(!is_null($this->get_cache_control))
			header(sf("Cache-Control: %s", $this->get_cache_control));
			
		if(!is_null($this->get_pragma))
			header(sf("Pragma: %s", $this->get_pragma));
		
	}	
	
	public function render() {
		
		pf("%s", $this->get_data);
	}
	
}
?>