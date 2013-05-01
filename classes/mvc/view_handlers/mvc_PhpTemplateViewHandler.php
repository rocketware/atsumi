<?php

class mvc_PhpTemplateViewHandler implements mvc_ViewHandlerInterface {
	
	// assoc array of templateRefs => file path
	private $templateFileMap 		= array();		
	
	// optional main template, template that includes $pageContent
	private $mainTemplate			= false;		
	
	// view/page data
	private $viewData				= array();
	
	private $surpressErrors			= true;

	const TEMPLATE_TYPE_FILE		= 1;
	const TEMPLATE_TYPE_STRING		= 2;
	

	public function __construct($mainTemplate = false, $templateFileMap = array(), $surpressErrors = true) {

		$this->templateFileMap 	= $templateFileMap;
		$this->mainTemplate		= $mainTemplate;
		$this->surpressErrors	= $surpressErrors;
	}
	
	
	/*
	 * Static: Processes a specific template file
	 */
	static public function processTemplateFile ($templateFile, $data, $supressErrors = false) {
		if (empty($templateFile)) return;
		
		extract($data, EXTR_SKIP);
		ob_start();
		try {
			
			if ($supressErrors)
				@include($templateFile);
			else 
				include($templateFile);
			
		} catch (Exception $e) { 

			Atsumi::error__listen($e);
			
			if (!$supressErrors)
				throw $e;
			else 
				Atsumi::error__recover($e);

			
			/*
			throw new mvc_ViewNotFoundException (
				"Can't find referenced template: ".$templateFile, 
				$templateFile
			); */
		}
		return ob_get_clean();
	}
	
	/*
	 * Static: Processes a specific template string
	* WARNING: You could create a PHP vulnerability by using this
	*/
	static public function processTemplateString ($templateString, $data, $supressErrors = false) {

		if (empty($templateString) || $templateString == '' || !$templateString) return;
		
		extract($data, EXTR_SKIP);
		ob_start();
		try {
			
			eval("?>".$templateString.'<?php ');
			
		} catch (Exception $e) {
	
			Atsumi::error__listen($e);
	
			if (!$supressErrors)	throw $e;
			else 					Atsumi::error__recover($e);
		}
		return ob_get_clean();
	}
	
	/*
	 * static: Prints a specific template file
	*/
	static public function renderTemplateFile ($templateFile, $data, $supressErrors = false) {
		print self::processTemplate($templateFile, $data, $supressErrors);
	}
	

	

	/*
	 * Processes a template-ref/template-file
	 * optionally includes view data
	 * Template can be a String (assumed file) or array
	 * if array should have values for key TYPE and TEMPLATE
	 * TYPE can be self::TEMPLATE_TYPE_FILE or TEMPLATE_TYPE_STRING
	 * TEMPLATE should be file path / template map ref / template string
	*/
	
	public function processTemplate ($template, $data = array(), $incViewData = false) {
		
		$templateFile = false;
		$templateString = false;
		
		if (is_array($template) && isset($template['TYPE']) && isset($template['TEMPLATE'])) {
			switch ($template['TYPE']) {
				
				case self::TEMPLATE_TYPE_FILE:
					$templateFile = $template['TEMPLATE'];
					break;

				case self::TEMPLATE_TYPE_STRING:
					$templateString = $template['TEMPLATE'];
					break;
						
			}
			
		} elseif (is_string($template)) $templateFile = $template;

		if ($incViewData)
			$data = array_merge($data, $this->viewData);
		
		if ($templateFile) {
			
			if (array_key_exists($templateFile, $this->templateFileMap))
				$templateFile = $this->templateFileMap[$templateFile];
			
	
			return self::processTemplateFile($templateFile, $data, $this->surpressErrors);
			
		} else if ($templateString) {

			return self::processTemplateString($templateString, $data, $this->surpressErrors);
				
		} else {
			throw new Exception ('Unknown template type');
		}
	}
	
	
	/*
	 * prints a template-ref/template-file
	 * optionally includes view data
	*/
	public function renderTemplate ($template, $data = array(), $incViewData = false) {
		print $this->processTemplate($template, $data, $incViewData);		
	}
	

	/*
	 * returns processed template
	 * optionally uses main template
	 * $viewTemplate can be type string (file path) or array
	*/
	public function process ($viewTemplate, $viewData, $mainTemplateOverride  = null) {
		
		$this->viewData = $viewData;
		$mainTemplate = is_null($mainTemplateOverride)?
			$this->mainTemplate:
			$mainTemplateOverride;
		
		// process the view tempalte
		$pageContent = $this->processTemplate(
			$viewTemplate, 
			array(), 
			true
		);
		// if there is a main template - process & render it
		if ($mainTemplate) 
			return $this->processTemplate(
				$mainTemplate, 
				array("pageContent"=>$pageContent), 
				true
			);

		// if not - render view template
		else return $pageContent;
	}

	/*
	 * prints a processed
	 * optionally uses main template
	*/
	public function render ($viewTemplate, $viewData, $mainTemplateOverride  = null) {
		print $this->process($viewTemplate, $viewData, $mainTemplateOverride);
	}
	

	/*
	 * set the main template
	*/
	public function setMainTemplate ($in) {
		$this->mainTemplate = $in;
	}
	
	
	
	

	
}

?>