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
 * ClamShell is the material used to create the white stones in Go the game from which Atsumi's
 * name is derived from. ClamShell is also Atsumi's html templating engine which was inspired by
 * Smarty and the .NET Framework render method with a few of our own smart ideas.
 *
 * ClamShell is an inheriting template engine, meaning templates can inherit off each other much
 * like the way classes inherit from each other in PHP
 * @package		Atsumi.Framework
 * @subpackage	MVC
 * @since		0.90
 */
class mvc_ClamShellHandler implements mvc_ViewHandlerInterface {
	/* CONSTANTS */

	const CACHE_NONE = 0;
	const CACHE_FLATFILE = 1;
	const CACHE_HANDER = 2;
	const CACHE_FUNCTION = 3;

	/* PROPERTIES */

	protected $templateDir;
	protected $compileDir;

	protected $cacheType = self::CACHE_NONE;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new mvc_ClamShellHandler instance
	 * Note: Directory strings that do not start with a '/' will be presumed relative to the
	 * atsumi workspace. Directory strings that do start with a '/' will be presumed to be
	 * absolute paths
	 * @param string $templateDir The directory that all templates are stored in
	 * @param string $compileDir The directory that ClamShell will write its compiled templates to
	 */
	public function __construct($templateDir, $compileDir) {
		$this->templateDir = (substr($templateDir, 0, 1) == '/' ? $templateDir : atsumi_Loader::getWorkspace().'/'.$templateDir);
		$this->compileDir = (substr($compileDir, 0, 1) == '/' ? $compileDir : atsumi_Loader::getWorkspace().'/'.$compileDir);;
	}

	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Renders a compiled template using the data provided
	 * Note: A template will be compiled if it has not alread been done, or recompiled if the
	 * compiled version is out of date.
	 * @param unknown_type $viewName
	 * @param unknown_type $viewData
	 */
	public function render($viewName, $viewData) {
	}
}
?>