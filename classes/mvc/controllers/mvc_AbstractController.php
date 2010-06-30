<?php

abstract class mvc_AbstractController {

	protected 	$app	 		= null;
	protected 	$errorHandler	= null;
	protected 	$viewHandler	= 'mvc_AtsumiViewHandler';
	protected 	$viewName		= null;
	protected 	$data			= array();

	protected 	$processedMethods = array();

	// GET FUNCTIONS

	public function get($key) {
		if(!array_key_exists($key, $this->data))
			throw new Exception("Item does not exist in view data..");
		return $this->data[$key];
	}

	public function getViewHandler() {
		return $this->viewHandler;
	}

	public function getView() {
		return $this->viewName;
	}

	public function getViewData() {
		return $this->data;
	}

	// SET FUNCTIONS

	// set a value on the view
	public function set($one, $two = null) {
		if(is_array($one)) {
			if(is_array($two)) return $this->setArray(array_combine($one, $two));
			else 	return $this->setArray($one);
		} else	return $this->setArray(array($one => $two));
	}

	protected function setViewHandler($viewHandler) {
		$this->viewHandler = $viewHandler;
	}

	public function setView($viewName) {
		$this->viewName = $viewName;
	}

	// set values on the view from array
	private function setArray($data) {
		foreach($data as $name => $value)
			$this->data[$name] = $value;

	}

	// set a value on the view
	public function setFlash($one, $two = null) {
		if(is_array($one)) {
			if(is_array($two)) return $this->setFlashArray(array_combine($one, $two));
			else 	return $this->setFlashArray($one);
		} else	return $this->setFlashArray(array($one => $two));
	}

	/**
	 * Sets a one time session stored peice of data
	 *
	 * @param $data Array One or more key value paires
	 * @return null
	 */
	protected function setFlashArray($data) {
		foreach($data as $key => $value)
			$_SESSION['__flash'][$key] = $value;
	}

	/* CONSTRUCTOR & DESTRUCTOR */

	function __construct($app, $errorHandler) {
		$this->app 			= $app;
		$this->errorHandler = $errorHandler;
	}

	/* METHODS */

	public function addProcessedMethod($method, $args) {
		$this->processedMethods[] = array('name' => $method, 'args' => $args);
	}

	// pre & post process for abstraction
	public function preProcess() {}
	public function postProcess() {}

	// pre & post render for abstraction
	public function preRender() {}
	public function postRender() {}

	public function publishFlashData() {

		if(isset($_SESSION['__flash'])) {
			foreach($_SESSION['__flash'] as $key => $value)

				$this->set($key, $value);

			unset($_SESSION['__flash']);
		}
	}

	// push a value on an array to the view
	public function push($one, $two = null) {
		$this->data[$one][] = $two;
	}

	// push a value on an array to the view
	public function pushFlash($name, $two = null) {

		$_SESSION['__flash'][$name][] = $two;
	}

	// optionally deal with 404s on this controller
	public function methodlessRequest($path) {
		throw new app_PageNotFoundException($this, __FUNCTION__);
	}

	public function reload($anchor = null) {
		unset($_POST);

		$anchor = !is_null($anchor) ? "#".$anchor:"";

		array_key_exists('REDIRECT_SCRIPT_URI',$_SERVER) ?
			header('Location: ' . $_SERVER['REDIRECT_SCRIPT_URI']. $anchor)
		: 	header('Location: ' . 'http://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']. $anchor);

		exit;
	}

	// redirect to a new page
	public function redirect($url, $httpResponseCode = atsumi_Http::REDIRECT_FOUND) {

		if(substr($url, 0, 7) == "http://") {
			header('Location: ' . $url, true, $httpResponseCode);
			exit;
		}

		$domain = $_SERVER['HTTP_HOST'];
		unset($_POST);
		header('Location: ' . 'http://' . $domain.$url, true, $httpResponseCode);
		exit;
	}

	// controller page data
	public function getData($method = null, $controller = null) {

		$parserData = Atsumi::app__getParserMetaData();
		$stack = $parserData['spec'];

		for($i = count($stack)-1; $i >= 0; $i--) {
			$entity = $stack[$i];
			/* if they haven't specified a controllero or it's specified and matches then break */
			if(is_null($controller) || $entity['controller'] == $controller) break;
		}

		// TODO: Give this it's own exceptions!

		if(!is_null($controller) && $entity['controller'] != $controller) throw new Exception("Controller not found...");

		$controller = is_null($controller)?$this:new $controller($this->app, $this->errorHandler);


		// TODO: This only works for page_ not other methods....

		$method = is_null($method)?substr($entity['method'][count($entity['method'])-1]['name'],5):$method;
		$methodName = sf('data_%s',$method);
		$pageMethod = null;

		// loop through methods to find the corect one...
		foreach($entity['method'] as $methodData)
			if($methodData['name'] == sf('page_%s',$method)) {
				$pageMethod = $methodData;
				break;
			}

		// TODO: Give this it's own exceptions!
		if(is_null($methodData)) throw new Exception("Method not found...");

		return call_user_func_array(array($controller, $methodName), $methodData['args']);
	}
}
?>