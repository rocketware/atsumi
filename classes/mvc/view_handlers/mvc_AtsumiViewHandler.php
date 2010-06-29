<?php

class mvc_AtsumiViewHandler implements mvc_ViewHandlerInterface {

	public function render($viewName, $viewData) {

		// atsumi view specific handler
		if(!class_exists($viewName))
			throw new mvc_ViewNotFoundException(sf("View class does not exist: '%s'",$viewName), $viewName);

		$viewClass = new $viewName($viewData);
		$viewClass->setHeaders();
		$viewClass->render();

	}




}

?>