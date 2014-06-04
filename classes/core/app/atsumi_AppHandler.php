<?php
/**
 * @version		0.90
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * The application handler controls the parsing of the uri, the processing of the controller and
 * the rendering of the resulting page/data output. It also manages transfer of data between the
 * three areas
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_AppHandler {

	/* PROPERTIES */

	/**
	 * A handle to the project settings
	 * @access private
	 * @var atsumi_AbstractAppSettings
	 */
	private $settings;

	/**
	 * A handle to the error handler
	 * Note: This is currently need to allow the controller to change error handling practices
	 * @access private
	 * @var atsumi_ErrorHandler
	 */
	private $errorHandler;

	/**
	 * The uri to be parsed and processed
	 * @access private
	 * @var string
	 */
	private $uri;

	/**
	 * The command to be parsed and processed
	 * @access private
	 * @var string
	 */
	private $command;

	/**
	 * Holds the base path of atsumi relative to the domain
	 * @access private
	 * @var string
	 */
	private $baseUri;

	/**
	 * The uri parser classname or instance to use
	 * @access private
	 * @var string|object
	 */
	private $uriParser = 'uriparser_Gyokuro';

	/**
	 * The command parser classname or instance to use
	 * @access private
	 * @var string|object
	 */
	private $claParser = 'claparser_Standard';

	/**
	 * The controller instance generated based on the parser data
	 * @access private
	 * @var object
	 */
	private $controller;

	/**
	 * Controller, Method and Args returned by the parser
	 * @access private
	 * @var array
	 */
	private $parserData;

	/**
	 * Trace data returned by the parser
	 * @access private
	 * @var array
	 */
	private $parserMetaData = null;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new app_handler instance
	 * @access public
	 * @param atsumi_AbstractAppSettings $settings The applications settings
	 * @param atsumi_ErrorHandler $errorHandler The applications error handler
	 */
	public function __construct($settings, $errorHandler) {
		if(!($settings instanceof atsumi_AbstractAppSettings))
			throw new Exception('Settings must be an instance of atsumi_AbstractAppSettings');

		$this->settings = $settings;
		$this->errorHandler = $errorHandler;
	}

	/* GET METHODS */

	/**
	 * Returns the parser return data
	 * @access public
	 * @return array|null The parser return data, or null on error
	 */
	public function getParserData() {
		return $this->parserData;
	}
	
	/**
	 * Returns the parser return data
	 * @access public
	 * @return array|null The parser return data, or null on error
	 */
	public function getParserMetaData() {
		return $this->parserMetaData;
	}

	/**
	 * get the base path of atsumi relative to the domain for if atsumi is running in a subdirectory
	 * @access public
	 * @param string $basePath
	 */
	public function getBaseUri() {
		return $this->baseUri;
	}
	
	// SET FUNCTIONS

	/**
	 * Sets the command to be parsed and processed
	 * @access public
	 * @param string $path
	 */
	public function setCommand($command) {
		$this->command = $command;
	}


	/**
	 * Sets the uri to be parsed and processed
	 * @access public
	 * @param string $path
	 */
	public function setUri($uri) {
		$this->uri = !empty($this->baseUri) &&
			substr($uri, 0, strlen($this->baseUri)) == $this->baseUri ?
				'/'.substr($uri, strlen($this->baseUri)) : $uri;
	}

	/**
	 * Sets the base path of atsumi relative to the domain for if atsumi is running in a subdirectory
	 * @access public
	 * @param string $basePath
	 */
	public function setBaseUri($baseUri) {
		$this->baseUri = $baseUri;
	}

	/**
	 * Sets the uri parser to be used to parse the uri
	 * @access public
	 * @param string|uriparser_Interface $uriParser A classname or parser object
	 */
	public function setUriParser($uriParser) {
		if(is_string($uriParser) && class_exists($uriParser))
			$uriParser = new $uriParser();

		if(!is_object($uriParser))
			throw new Exception(__FUNCTION__.'() : $uriParser must be either a classname or parser object');

		if(!($uriParser instanceof uriparser_Interface))
			throw new Exception(__FUNCTION__.'() : URI Parser must implement uriparser_Interface');

		$this->uriParser = $uriParser;
	}

	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Uses the choosen parser to generate a path to a controller and method
	 * @access public
	 * @param string $controller The name of the final controller
	 * @param string $method The name of the method that is to be called on the controller
	 */
	public function createUri($controller, $method) {
		$path = $this->uriParser->createUri($this->settings->init_specification, $controller, $method);
		return($this->baseUri ? $this->baseUri : '/').$path;
	}

	/**
	 * Short hand used to set the uri, parse it and process it
	 * @access public
	 * @param string $uri The uri to processed
	 */
	public function go($uri) {
		if(isset($this->settings->get_cli) && $this->settings->get_cli === true) {
			$this->setBaseUri('.');
			$this->setCommand($uri);
			$this->parseCommand();
		} else {
			$scriptArr = explode('/',$_SERVER['SCRIPT_NAME']);
			$baseUri = str_replace(array($_SERVER['DOCUMENT_ROOT'],  end($scriptArr)), '', $_SERVER['SCRIPT_FILENAME']);
			$this->setBaseUri($baseUri);
	
			$this->setUri($uri);
			$this->parseUri();
		}
		$this->process();
	}

	/**
	 * Parses the set uri and uses the contained settings to derive a controller and method to be processed
	 * @access public
	 */
	public function parseUri() {
		atsumi_Debug::startTimer('app:parse:uri');

		if(!in_array('uriparser_Interface', class_implements($this->uriParser)))
			throw new Exception('URI Parser must implement uriparser_Interface');

		// Create the object if it is not a
		if(is_string($this->uriParser))
			$this->uriParser = new $this->uriParser;

		$parseData = $this->uriParser->parseUri($this->uri, $this->settings->init_specification);

		$this->parserData = array(
			'controller'	=> $parseData['controller'],
			'method'		=> $parseData['method'],
			'args'			=> $parseData['args']
		);
		$this->parserMetaData = $parseData['meta'];
		atsumi_Debug::setParserData($parseData);

		atsumi_Debug::record('Uri Parsing',
			'Uri was parsed to determine the controller, method and args.',
			array_merge(array('path' => $this->uri), $this->parserData),
			'app:parse:uri'
		);
	}

	public function parseCommand() {
		atsumi_Debug::startTimer('app:parse:command');
		if(!in_array('claparser_Interface', class_implements($this->claParser))) {
			throw new Exception('Command Line Argument parser must implement claparser_Interface');
		}
		if(is_string($this->claParser)) {
			$this->claParser = new $this->claParser();
		}
		$parseData = $this->claParser->parseCommand($this->command, $this->settings->init_specification);

		$this->parserData = array(
			'controller'	=>	$parseData['controller'],
			'method'	=> 	$parseData['method'],
			'args'		=> 	$parseData['args']
		);

		atsumi_Debug::setParserData($parseData);
		atsumi_Debug::record('Command Parsing',
			'Command was parsed to determine the controller, method and args.',
			array_merge(array('path' => $this->command), $this->parserData),
			'app:parse:command'
		);
	
	}

	/**
	 * Processes the controller and method choosen by the parser
	 * Note: parseUri method must be execute before this method
	 * @access public
	 */
	public function process() {

		// Could possibly be a fragment of the spec
		if(!is_string($this->parserData['controller']))
			throw new app_PageNotFoundException('Path parsing error, please report to developement team');
		if(!class_exists($this->parserData['controller']))
			throw new app_PageNotFoundException('Could not find required controller: '.$this->parserData['controller']);

		$classname = $this->parserData['controller'];
		$this->controller = new $classname($this->settings, $this->errorHandler);

		if(!method_exists($this->controller, $this->parserData['method']))
			throw new app_PageNotFoundException();

		// Get the debugger and start a timer for processing
		atsumi_Debug::startTimer('app:controller:processing');

		// Add the method to the list of processed methods
		$this->controller->addProcessedMethod($this->parserData['method'], $this->parserData['args']);

		// Time and execute the pre process
		atsumi_Debug::startTimer('app:controller:preProcess');
		$this->controller->preProcess();
		atsumi_Debug::record('Controller PreProcess', 'Before the controllers method was called the pre-process function was executed', null, 'app:controller:preProcess');

		// Time and execute the controllers method
		atsumi_Debug::startTimer('app:controller:method');
		$this->controller->processRequest($this->parserData['method'], $this->parserData['args']);
		atsumi_Debug::record('Controller Method', 'The controllers requested method was executed', null, 'app:controller:method');

		// Time and execute the post process
		atsumi_Debug::startTimer('app:controller:postProcess');
		$this->controller->postProcess();
		atsumi_Debug::record('Controller PostProcess', 'After the controllers method was called the post-process function was executed', null, 'app:controller:postProcess');

		// Log the whole processing time
		atsumi_Debug::record('Controller Processing Complete', 'All processing was completed successfully', null, 'app:controller:processing');
	}

	/**
	 * Calls render on the processed controller
	 * Note: process method must be execute before this method
	 * @access public
	 */
	public function render() {

		// Time and execute the pre render
		atsumi_Debug::startTimer('app:controller:preRender');
		$this->controller->publishFlashData();
		$this->controller->preRender();
		atsumi_Debug::record('Controller PreRender', 'Before rendering was processed the pre-render function was executed', null, 'app:controller:preRender');

		$viewHandler	= $this->controller->getViewHandler();
		$view			= $this->controller->getView();

		if(!in_array('mvc_ViewHandlerInterface', class_implements($viewHandler)))
			throw new Exception('View handler must implement mvc_ViewHandlerInterface');


		if(is_string($viewHandler))
			$viewHandler = new $viewHandler;

		if(is_null($view))
			throw new mvc_NoViewSpecifiedException('A view has not been declared', $this->parserData['method']);


		// Get the debugger and start a timer for rendering
		atsumi_Debug::startTimer('app:controller:rendering');

		$viewData = $this->controller->getViewData();
		atsumi_Debug::setViewData($viewData);


		// Time and execute the view handler
		atsumi_Debug::startTimer('app:controller:render');
		$viewHandler->render($view, $viewData);
		atsumi_Debug::record('Rendering', sf('Rendering was performed by the %s view handler', get_class($viewHandler)), null, 'app:controller:render');

		// Time and execute the post render
		atsumi_Debug::startTimer('app:controller:postRender');
		$this->controller->postRender();
		atsumi_Debug::record('Controller PostRender', 'After the rendering was processed the post-render function was executed', null, 'app:controller:postRender');

		// Log the whole processing time
		atsumi_Debug::record('Rendering Complete', 'All rendering was completed successfully', null, 'app:controller:rendering');
	}
}
?>
