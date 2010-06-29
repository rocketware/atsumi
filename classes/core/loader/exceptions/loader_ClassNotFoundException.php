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
 * Thrown by the atsumi loader when it cannot find a class
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class loader_ClassNotFoundException extends atsumi_AbstractException {
	/* PROPERTIES */

	/**
	 * The name of the class that could not be found
	 * @access private
	 * @var string
	 */
	private $className;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new exception_ClassNotFound instance
	 * @access public
	 * @param string $message The message to be displayed to the user
	 * @param string $className The name of the class that was not found
	 * @param integer $code A code representation of the error
	 */
	public function __construct($message = null, $className, $code = 0) {
		$this->className = $className;
		parent::__construct($message, $code);
	}

	/* METHODS */

	/**
	 * Returns further information on how to solve the the exception
	 * @access public
	 * @param string $contentType The context type(html|text)
	 * @return string Information about the exception
	 */
	public function getInstructions($contentType) {
		switch($contentType) {
			default:
			case 'text/plain':
				return sf('--Description\nYou have referenced a class \'%s\' that does not exist or has not been loaded. You need to create the class in a folder within the classes/ directory. Make sure you name the file the same as the class name followed by a .php extension. Once the file has been created and you\'ve added code to it you need to make sure you are loading the file using Atsumi\'s load method. If you want the folder of classes to be available site wide then add the load code to your index.php file.\n
--Example Code to create a basic controller\n
	class %s extends mvc_AbstractController {
		/* ... put controller code here ... */
	}

--Example Code to create a basic html view\n
	class %s extends mvc_HtmlView {
		public function renderBodyContent() {
			/* ... put view code here ... */
		}
	}

--Example code to load a folder containing your classes\n
	/* exmaple folders: models, views, controllers. These folders should exist in the \'classes/\' folder */
	Atsumi::load(\'
		myproject: models views controllers
	\');
', $this->className, $this->className, $this->className);
				break;
			case 'text/html':
				return sf('<h4>Description</h4><p>You have referenced a class \'<strong>%s</strong>\' that does not exist or has not been loaded. You need to create the class in a folder within the classes/ directory. Make sure you name the file the same as the class name followed by a .php extension. Once the file has been created and you\'ve added code to it you need to make sure you are loading the file using Atsumi\'s load method. If you want the folder of classes to be available site wide then add the load code to your index.php file.</p>
<h4>Example Code to create a basic controller</h4>
<pre class="code">
class %s extends mvc_AbstractController {
		/* ... put controller code here ... */
}
</pre>
<h4>Example Code to create a basic html view</h4>
<pre class="code">
class %s extends mvc_HtmlView {
		public function renderBodyContent() {
			/* ... put view code here ... */
		}
}
</pre>
<h4>Example code to load a folder containing your classes</h4>
<pre class="code">
/* exmaple folders: models, views, controllers. These folders should exist in the \'classes/\' folder */
Atsumi::load(\'
		myproject: models views controllers
\');
</pre>
', $this->className, $this->className, $this->className);
				break;
		}
	}
}
?>