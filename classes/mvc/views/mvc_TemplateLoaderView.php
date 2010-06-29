<?php
/*
 * Created on 14 Mar 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class mvc_TemplateLoaderView extends mvc_AbstractView {

	public function render() {
		
		$html = mvc_TemplateLoaderView::renderTemplate($this->data, $this->data['template']);
		

		
		pfl("%s", $html);
	}
	
	static function parseTemplate($tpl) {
	
		preg_match_all("|{{([^}]+)}}|U",
					$tpl,
					$tplSyntax, PREG_PATTERN_ORDER);

		$syntaxOut = array();
		
		for($i = 0; $i < count($tplSyntax[0]); $i++) {

			$data = explode(" ", $tplSyntax[1][$i], 2);
			$argsArr = isset($data[1])? explode("|",$data[1]):null;
			
			$syntaxOut[] =  array(		"original"	=> $tplSyntax[0][$i],
										"stripped"	=> $tplSyntax[1][$i],
										"method"	=> $data[0],
										"args"		=> isset($argsArr[0])?$argsArr[0]:null,
										"options"	=> isset($argsArr[1])?$argsArr[1]:null
									);		
								
			
			
		}
		return $syntaxOut;
		
	}
	/* loads a template file and any includes */
	static function recurrsiveLoadTemplate($tplPath) {
		
		// fetch the template
		$tpl = @file_get_contents(getcwd() . $tplPath);
		
		if($tpl === false) 
			throw new Exception(sf("Template file not found: %s", getcwd() . $tplPath));
		
		// read the template syntax
		$syntax = mvc_TemplateLoaderView::parseTemplate($tpl);
		
		// action any includes...
		foreach($syntax as $command) {
			if($command['method'] == "include") {
				preg_match(':file=[\'|"]{1}(.*)[\'|"]{1}:',$command['args'], $file);
				$tpl = str_replace($command['original'], mvc_TemplateLoaderView::recurrsiveLoadTemplate($file[1]), $tpl);
			}	
		}
		
		return $tpl;
		
		
	}
	
	static function renderTemplate($dataArr, $templatePath) {
		
		$template 		= mvc_TemplateLoaderView::recurrsiveLoadTemplate($templatePath);
		$templateSyntax = mvc_TemplateLoaderView::parseTemplate($template);
		
		// TODO: This should really be modular...
		// action any variables...
		foreach($templateSyntax as $command) {
			if($command['method'] == "var") {
				
				$template = str_replace(	$command['original'], 
											(isset($dataArr[$command['args']])? sf("%s",$dataArr[$command['args']]): ""), 
											$template
										);
			}	
		}
		
		
		return $template;
 	}
		
}

?>
