<?php

class mvc_PhpTemplateViewHandler implements mvc_ViewHandlerInterface {
	
	// assoc array of templateRefs => file path
	private $templateFileMap 		= array();		
	
	// optional main template, template that includes $pageContent
	private $mainTemplate			= false;		
	
	// view/page data
	private $viewData				= array();
	
	private $surpressErrors			= true;


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
	 * static: Prints a specific template file
	*/
	static public function renderTemplateFile ($templateFile, $data, $supressErrors = false) {
		print self::processTemplate($templateFile, $data, $supressErrors);
	}
	

	/*
	 * Processes a template-ref/template-file
	 * optionally includes view data
	*/
	public function processTemplate ($template, $data = array(), $incViewData = false) {
		
		if (array_key_exists($template, $this->templateFileMap))
			$template = $this->templateFileMap[$template];
		
		if ($incViewData) 
			$data = array_merge($data, $this->viewData);

		return self::processTemplateFile($template, $data, $this->surpressErrors);
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