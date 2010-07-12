<?php

class widget_NameElement extends widget_AbstractElement {
	protected $middleName = false;
	// TODO: not using gender...
	protected $titles = array(	"mr"  	=> array("text"=>"Mr","gender"=>"male"),
								"mrs"  	=> array("text"=>"Mrs","gender"=>"female"),
								"ms"  	=> array("text"=>"Ms","gender"=>"female"),
								"miss"  => array("text"=>"Miss","gender"=>"female"),
								"dr"  	=> array("text"=>"Dr","gender"=>null)
							);

	protected 	$firstNameDefault = "First Name";
	protected 	$lastNameDefault = "Last Name";
	private 	$jsDefaults = true;


	public function __construct($args) {
		if(array_key_exists('middleName',$args)) $this->middleName = $args['middleName'];
	}
	function renderElement() {


		$name = $this->getValue();
			if(!is_array($name)) $name = array();
			if(!array_key_exists('title',$name)) $name['title'] = null;
			if(!array_key_exists('firstName',$name)) $name['firstName'] = null;
			if(!array_key_exists('lastName',$name)) $name['lastName'] = null;

			$titleOptions = sfl("<option value='' >Title</option>");
			foreach($this->titles as $title => $data) {
				if($name['title'] == $title)
					$titleOptions .= sfl("<option value='%s' selected='selected'>%s</option>",
											parent::makeInputSafe($title),
											$data['text']
					);
				else
					$titleOptions .= sfl("<option value='%s'>%s</option>",
											parent::makeInputSafe($title),
											$data['text']
					);
			}


			$out =	sfl("<select name='%s[title]' id='form_%s' class='inputName inputNameTitle' >%s</select>",
							$this->getName(), $this->getName(), $titleOptions
						);

			$out .=	sfl("<input type='text' name='%s[firstName]' value='%s' id='form_%s' class='inputName inputNameFirst' class='text' />",
							$this->getName(), parent::makeInputSafe($name['firstName']), $this->getName()
						);

			$out .=	sfl("<input type='text' name='%s[lastName]' value='%s' id='form_%s' class='inputName inputNameLast' class='text' />",
							$this->getName(), parent::makeInputSafe($name['lastName']), $this->getName()
						);

			return $out;
	}

	// TODO: this.....
	public function get2Value() {
		$value =(is_null($this->value) || $this->getForceDefault()) ? $this->defaultValue : $this->value;


	}
}

?>