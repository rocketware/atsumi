<?php

class widget_Form {

	private $name 			= null;
	private $title 			= null;

	private $elements 		= array();
	private $elementMap 	= array();
	private $method 		= 'POST';
	private $userInput 		= array();		// user entered data
	private $userFiles 		= array();		// user uploaded data
	private $validates		= true;
	private $submitText		= 'Submit';
	private $encoding		= 'application/x-www-form-urlencoded';
	private $forceDefaults	= false;
	private $submitted		= false;
	private $ancorJump		= false;		// will the form jump to the ancor?
	private $actionPath		= '';
	private $cssClasses		= array();

	public function __construct($formName = null, $autoLoadFormData = true) {
		if(!is_null($formName)) $this->setName($formName);
		if($autoLoadFormData) $this->setFormDataFromMethod();
	}

	public function __get($name) {
		$matches = null;
		if(preg_match('/^value_(.+)$/', $name, $matches)) {
			return call_user_func(array($this, 'value'), $matches [1]);			
		} elseif (preg_match('/^element_(.+)$/', $name, $matches)) {
			return call_user_func(array($this, 'element'), $matches [1]);
		} else throw new Exception('Undefined method:' . $name);
	}

	public function setName($in) {
		if(!is_string($in)) throw new Exception('Form name must be of type String');
		$this->name = $in;
	}

	public function setTitle($in) {
		if(!is_string($in)) throw new Exception('Form Title must be of type String');
		$this->title = $in;
	}

	public function setActionPath($path) {
		$this->actionPath = $path;
	}

	public function getTitle() {
		return $this->title;
	}

	public function addCssClass ($className) {
		 $this->cssClasses[] = $className;
	}

	public function setFormDataFromMethod() {

		switch($this->method) {
			case 'POST':
				$this->userInput =(is_null($_POST) || empty($_POST) || !isset($_POST['submitted__'.$this->name])) ? array() : $_POST;
				$this->userFiles =(is_null($_FILES) || empty($_FILES) || !isset($_POST['submitted__'.$this->name])) ? array() : $_FILES;
				break;
			case 'GET':
				$this->userInput = (is_null($_GET) || empty($_GET) || !isset($_GET['submitted__'.$this->name])) ? array() : $_GET;
				$this->userFiles =(is_null($_FILES) || empty($_FILES) || !isset($_GET['submitted__'.$this->name])) ? array() : $_FILES;
				break;
		}
		$this->submitted =(!empty($this->userInput) || !empty($this->userFiles));
	}

	public function add($elementClass, $args) {
		if(!class_exists($elementClass)) throw new Exception(sf("Form Element does not exist : %s",$elementClass));

				// if the user has submitted data validate the element
		if(empty($this->userInput) && empty($this->userFiles))	$args['submitted'] = false;
		else	$args['submitted'] = true;

		//TODO: All of these special handles need to moved to the elements....
		if($elementClass == 'widget_FileElement')
			$this->encoding = 'multipart/form-data';

		$element = new $elementClass($args);
		$element->setSubmitted($this->getSubmitted());

		// validators to use on element
		if(array_key_exists('validators',$args))
			$element->setValidators($args['validators']);

		// name
			$element->setName($args['name']);

		// default value
		if(array_key_exists('default',$args))
			$element->setDefault($args['default']);

		// elements label
		if(array_key_exists('label',$args))
			$element->setLabel($args['label']);

		// css style
		if(array_key_exists('style',$args))
			$element->setStyle($args['style']);
			
		// css style
		if(array_key_exists('cssStyle',$args))
			$element->setCssStyle($args['cssStyle']);
		
		// tabindexes
		if(array_key_exists('tabindex',$args))
			$element->setTabindex($args['tabindex']);
			
		// css style
		if(array_key_exists('cssClass',$args))
			$element->setCssClass($args['cssClass']);
			
		if(array_key_exists('force_default', $args))
			$element->setForceDefault($args['force_default']);

		// If form not yet submitted element is prefilled with the value entered in spec..
		if(!$this->submitted && array_key_exists('name',$args) && array_key_exists('value',$args))
			 $this->userInput[$args['name']] =  $args['value'];
		try {
			$element->setValue($this->userInput, $this->userFiles);
		} catch(Exception $e) {
			$element->setError($e);
		}
		$element->validate();

		// save the new element to the form...
		$this->elements[] = $element;
		$this->elementMap[$element->getName()] = &$element;
	}

	public function remove($elementName) {
		if(!isset($this->elementMap[$elementName])) {
			throw new Exception('Cannot remove non-existant element');
		}
		for($i = 0; $i < count($this->elements); $i++) {
			$done = false;
			if($this->elementMap[$elementName] == $this->elements[$i]) {
				unset($this->elementMap[$elementName]);
				unset($this->elements[$i]);
				$done = true;
			}
			if($done) {
				$this->elements = array_values($this->elements);
				break;
			}
		}
	}

	public function value($elementName) {
		if(!array_key_exists($elementName, $this->elementMap))
			throw new Exception("Element not found: ".$elementName);

		return $this->elementMap[$elementName]->getValue();
	}

	/* Returns an element */
	public function element ($elementName) {
		
		if(!array_key_exists($elementName, $this->elementMap))
			throw new Exception("Element not found: ".$elementName);

		return $this->elementMap[$elementName];
	}

	public function getSubmitted() {
		return $this->submitted;
	}

	public function setForceDefaults() {
		$this->forceDefaults = true;
	}

	public function getSubmit() {
		return $this->submitText;
	}

	public function setAncorJump($jump) {
		$this->ancorJump = $jump;
	}

	public function setSubmit($in) {
		if(!is_string($in))
			throw new Exception("Submit text should be of type String");
		$this->submitText = $in;
	}

	public function getValidates() {
		foreach($this->elements as $row)
			if(!$row->getValidates()) $this->validates = false;

		return $this->validates;
	}

	public function completed() {
		if($this->getValidates() && $this->getSubmitted()) return true;
		return false;
	}

	public function failed() {
		return self::hasErrors();
	}

	public function hasErrors() {
		if(!$this->getValidates() && $this->getSubmitted()) return true;
		return false;
	}

	public function __toString() {
		return strval($this->render());
	}

	public function render() {

		// totally forgotten what this force defaults is, do we need it?...
		// forceDefaults is if you want to force the form to show its default values on reload
		// rather than showing the values the user has entered
		if($this->forceDefaults)
			foreach($this->elements as $row) {
					$row->setForceDefault(true);
					$row->validate();
			}

		$html = $this->getFormTop();

		foreach($this->elements as $element) {
			$html .= sfl("%s", $element->render());
		}

		$html .= $this->getFormBottom();
		return $html;
	}

	public function hasElement($elementName) {
		return array_key_exists($elementName, $this->elementMap);
	}
	public function getElement($elementName, $options = array()) {
		return $this->elementMap[$elementName]->render($options);
	}

	public function getFormTop() {

		$html = sf('<a name="form_%s"></a><form name="%s" id="%s" method="%s" action="%s" enctype="%s" class="form%s">',
			$this->name,
			$this->name,
			$this->name,
			$this->method,
			$this->ancorJump ? sf('%s#form_%s', $this->actionPath, $this->name) : $this->actionPath,
			$this->encoding,
			count($this->cssClasses)?' '.implode(' ', $this->cssClasses):''
		);

		// add a hidden field to verify if this form has been posted
		$html .= sfl("<input type='hidden' name='submitted__%s' value='true' />", $this->name);

		if(!is_null($this->getTitle()))
			$html .= sfl('<h2>%s</h2>\n', $this->getTitle());

		return $html;
	}

	public function getFormBottom() {

		// add the submit to the bottom of the form for now(will convert to element in next version)
		$html  = sfl('	<div class="submit rowSubmit">');
		$html .= sfl('		<input type="submit" class="button-submit" id="submit_%s" value="%s" />', $this->name, $this->getSubmit());
		$html .= sfl('	</div>');
		$html .= sfl('</form>');

		return $html;
	}
}
?>