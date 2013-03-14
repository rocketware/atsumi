<?php

class mvc_PhpTemplateViewHandler implements mvc_ViewHandlerInterface {
	
	// assoc array of templateRefs => file path
	private $templateFileMap 		= array();		
	
	// optional main template, template that includes $pageContent
	private $mainTemplate			= false;		
	
	// view/page data
	private $viewData				= array();

	
	public function __construct($mainTemplate = false, $templateFileMap = array()) {

		$this->templateFileMap 	= $templateFileMap;
		$this->mainTemplate		= $mainTemplate;

	}

	
	/*
	 * Static: Processes a specific template file
	 */
	static public function processTemplateFile ($templateFile, $data) {
		if (empty($templateFile)) return;
		
		extract($data, EXTR_SKIP);
		ob_start();
		try {
			@include($templateFile);
		} catch (Exception $e) { 
			throw new mvc_ViewNotFoundException (
				"Can't find referenced template: ".$pageTemplate, 
				$pageTemplate
			); 
		}
		return ob_get_clean();
	}

	/*
	 * static: Prints a specific template file
	*/
	static public function renderTemplateFile ($templateFile, $data) {
		print self::processTemplate($templateFile, $data);
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

		return self::processTemplateFile($template, $data);
	}
	
	
	/*
	 * prints a template-ref/template-file
	 * optionally includes view data
	*/
	public function renderTemplate ($template, $data = array(), $incViewData = false) {
		print $this->processTemplate($template, $data, $incViewData);		
	}
	

	
	public function render($viewTemplate, $viewData) {
		
		$this->viewData = $viewData;
		
		// process the view tempalte
		$pageContent = $this->processTemplate(
			$viewTemplate, 
			array(), 
			true
		);

		// if there is a main template - process & render it
		if ($this->mainTemplate) 
			$this->renderTemplate(
				$this->mainTemplate, 
				array("pageContent"=>$pageContent), 
				true
			);

		// if not - render view template
		else print $pageContent;
	}
}

?>