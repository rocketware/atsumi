<?php

class mvc_SmartyViewHandler implements mvc_ViewHandlerInterface {

	private $smarty;

	public function __construct(Smarty $smarty) {

		$this->smarty = $smarty;

	}

	public function render($viewName, $viewData) {

		// atsumi view specific handler
		if(is_null($this->smarty))
			throw new mvc_TemplateEngineNotSupplied("Smarty has not been supplied");

		$this->smarty->clearAllAssign();
		$this->smarty->assign($viewData);
		$this->smarty->display($viewName);

	}

}

?>