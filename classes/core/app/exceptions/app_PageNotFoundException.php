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
 * Exception thrown by the Atsumi framework when a page cannot be found
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class app_PageNotFoundException extends atsumi_AbstractException {
	/* PROPERTIES */

	/**
	 * The controller that was being called
	 * @access protected
	 * @var string
	 */
	protected $controller;

	/**
	 * The method that was being called
	 * @access protected
	 * @var string
	 */
	protected $method;
	protected $args = array();

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new app_PageNotFoundException instance
	 * @access public
	 * @param string $controller The name of the controller being called
	 * @param string $method The name of the method being called
	 */
	public function __construct($_) {

		parent::__construct('ERROR 404: Page Not Found');
	}

	/* GET METHODS */

	/**
	 * Returns further information on how to solve the the exception
	 * @access public
	 * @param string $contentType The context type(html|text)
	 * @return string Information about the exception
	 */
	public function getInstructions($contentType) {

		// should the below (determining the method) be the responsibility of the parser?
		$parserData = Atsumi::app__getParserMetaData();
		$parserData = end($parserData['stack']);

		$this->controller 	= $parserData['controller'];
		$this->method 		= $parserData['method'];

		if ($this->method == 'methodlessRequest') {
			$methodArr = explode('/',$parserData['args'][0]);

			// TODO: This should talk back to the URI parser asking it to parse a page method.
			$this->method = 'page_'.$methodArr[1];
			$this->args = array_slice($parserData['args'], 1);
		}

		// create string of args
		$argString = '';
		for($i = 0; $i < count($this->args); $i++)
			$argString .= ($argString==''?'':', ').'$arg'.strval($i+1);

		switch($contentType) {
			default:
			case 'text/plain':
				return sf('--Description\nYour choosen paser has determined that the \'%s\' method of the \'%s\' controller should be called but the method or controller cannot be found. Please make sure it exists.
--Example Code to create the controller and method\n
	class %s extends mvc_AbstractController {
		// The method to be called
		public function page_%s {
			// Don\'t forget to set your view
			$this->setView(\'name_of_view\');
		}
	}

--Example code to load a folder containing your controller\n
	/* exmaple folders: models, views, controllers. These folders should exist in the \'classes/\' folder */
	Atsumi::references(array(\'myproject\' => \'models views controllers\'));
', $this->method, $this->controller, $this->controller, $this->method);
				break;
			case 'text/html':
				return sf('<h4>Description</h4><p>Your choosen paser has determined that the \'<strong>%s</strong>\' method of the \'<strong>%s</strong>\' controller should be called but the method or controller cannot be found. Please make sure it exists.</p>
<h4>Example Code to create the controller and method</h4>
<pre class="code">
class %s extends mvc_AbstractController {
	// The method to be called
	public function %s (%s) {
		// Don\'t forget to set your view
		$this->setView(\'name_of_view\');
	}
}
</pre>
<h4>Example code to load a folder containing your controller</h4>
<pre class="code">
/* exmaple folders: models, views, controllers. These folders should exist in the \'classes/\' folder */
Atsumi::references(array(\'myproject\' => \'models views controllers\'));
</pre>

', $this->method, $this->controller, $this->controller, $this->method, $argString);
				break;
		}
	}

	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */
}
?>