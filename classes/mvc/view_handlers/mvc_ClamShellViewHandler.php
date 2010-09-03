<?php
/**
 * File defines all functionaility of the mvc_ClamShellHandler class.
 * @version		0.90
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

atsumi_Loader::references(atsumi_Loader::getAtsumiDir(), 'jsmin');
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
class mvc_ClamShellViewHandler implements mvc_ViewHandlerInterface {
	/* CONSTANTS */

	const CACHE_NONE		= 0;
	const CACHE_FLATFILE	= 1;
	const CACHE_HANDER		= 2;
	const CACHE_FUNCTION	= 3;

	const F_CONDENCE		= 1;
	const F_RM_COMMENTS		= 2;
	const F_MINIFY_JS		= 4;
	const F_MINIFY_CSS		= 8;
	const F_RM_EXRTA_WHITE	= 16;
	const F_ALL				= 31;

	/* PROPERTIES */

	protected $templateDir;
	protected $compileDir;
	protected $flags;
	protected $content = array();

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
	public function __construct($templateDir, $compileDir, $flags = 0) {
		$this->flags = $flags;
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
		$template = $this->templateDir.'/'.$viewName;
		$compiled = $this->compileDir.'/'.str_replace('.', '-', $viewName).'.clam';
		if(!file_exists($template))
			mvc_ViewNotFoundException(sf('View class does not exist: \'%s\'', $viewName), $viewName);

		if(!file_exists($compiled) || filemtime($compiled) < filemtime($template)) {
			echo $this->buildTemplate($template);
		}
		else {
			// Populate the template
			$compiled_data = file_get_contents($compiled);
			explode("\n", $compiled_data, 2);
			echo $compiled_data;
		}
	}

	public function buildTemplate($template) {
		$template_data = file_get_contents($template);
		return $this->tagSearch($template_data);
	}

	public function cleanData($data) {
		// Remove any PHP code
		$data = preg_replace('#<\?.*\?>#', '', $data);

		// Remove new line chars
		if($this->flags & self::F_CONDENCE)
			$data = str_replace(array("\n", "\r", "\t"), '', $data);

		// Remove extra whitespace
		if($this->flags & self::F_RM_EXRTA_WHITE) {
			$data = preg_replace('#((<[^>]*>) +)|( +(</[^/>]*>))#', '\\2', $data);
			$data = preg_replace('# +#', ' ', $data);
		}

		if($this->flags & self::F_RM_COMMENTS)
			$data = preg_replace('#<!--[^@](.*)-->#', '', $data);

		if($this->flags & self::F_MINIFY_JS)
			$data = preg_replace_callback('#(<script[^>]*>)(.*)(</script>)#', array($this, 'MinifyJs'), $data);

		if($this->flags & self::F_MINIFY_CSS)
			$data = preg_replace_callback('#(<style[^>]*>)(.*)(</style>)#', array($this, 'MinifyJs'), $data);

		$data = preg_replace_callback('#<!--@([a-zA-Z]*)([^@]*)/-->#', array($this, 'ProcessScTags'), $data);

		return $data;
	}

	public function tagSearch($data) {
		// TODO: Write system to correcty parse tags with closing tags
		return $this->cleanData($data);
	}

	public function ProcessScTags($matches) {
		switch(strtolower($matches[1])) {
			case 'contentplace':
				$params = $this->parseParams($matches[2]);
				if(array_key_exists('id', $params))
					return $this->getContent($params['id']);
				break;
			default:
				return '';
				break;
		}
	}

	public function MinifyJs($string) {
		if(is_array($string)) {
			array_shift($string);

			if(strlen(trim($string[1])) > 1)
				$string[1] = JSMin::minify($string[1]);

			return implode('', $string);
		}

		return JSMin::minify($string);
	}

	public function MinifyCss($string) {
		if(is_array($string)) {
			array_shift($string);

			if(strlen(trim($string[1])) > 1)
				$string[1] = $string[1];

			return implode('', $string);
		}

		return $string;
	}

	protected function getContent($id) {
		$id = strtolower(trim($id));
		return (array_key_exists($id, $this->content) ? $this->content[$id] : '');
	}

	protected function setContent($id, $data) {
		$this->content[strtolower(trim($id))] = $data;
	}

	protected function hasContent($id) {
		return array_key_exists(strtolower(trim($id)), $this->content);
	}

	protected function parseParams($string) {
		$params = array();
		$count = preg_match_all('#([a-zA-Z]*)="([^"]*)"#', $string, $matches);
		for($i = 0; $i < $count; $i++)
			$params[strtolower(trim($matches[1][$i]))] = $matches[2][$i];

		return $params;
	}
}
?>