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
 * The atsumi auto load class is used to load classes into the framework when they are required
 * but not already included
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_Loader {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * What the developer has name the atsumi build
	 * @access protected
	 * @var string
	 */
	protected $atsumiDir;

	/**
	 * The workspace that atsumi and all related projects is held within
	 * @access protected
	 * @var string
	 */
	protected $workspace;

	/**
	 * A list of all classes that have been processed by the loader (not loaded)
	 * @access protected
	 * @var array
	 */
	protected $classes = array();

	/**
	 * A list of all paths that have been processed by the loader
	 * @access protected
	 * @var array
	 */
	protected $processedPaths = array();

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new atsumi_Loader instance
	 * Note: Private prevents anything externally creating the debugger. Use static functions.
	 * @access private
	 */
	private function __construct() {
		$this->workspace = $this->findWorkspace();
	}

	// GET FUNCTIONS

	/**
	 * Creates and/or returns a singlton instance of the class
	 * Note: Protected prevents anything externally calling this function. Use static functions.
	 * @access protected
	 * @return atsumi_Loader A singlton instance of the loader
	 */
	protected static function getInstance() {
		static $sInstance;

		if(!is_object($sInstance))
			$sInstance = new self;

		return $sInstance;
	}

	/**
	 * Gets the name of the atsumi directory, incase it has been name differently by the developer
	 * @access public
	 * @return string The atsumi directory name
	 */
	public function _getAtsumiDir() {
		return $this->atsumiDir;
	}

	/**
	 * Gets the workspace of atsumi and the all related projects
	 * @access public
	 * @return string A absolute path to the workspace
	 */
	public function _getWorkspace() {
		return $this->workspace;
	}

	/* MAGIC METHODS */

	/**
	 * PHP Magic function, used to call methods on singleton staticly
	 * @access public
	 * @param string $name The name of the method being called
	 * @param array $arguments The arguments being passed to the method
	 * @return mixed The result of the function call
	 */
	public static function __callStatic($name, $arguments) {
		$instance = self::getInstance();

		if(method_exists($instance, '_'.$name))
			return call_user_func_array(array($instance, '_'.$name), $arguments);

		throw new Exception('Undefined call to atsumi_Debug::'.$name);
	}

	/* METHODS */

	/**
	 * Return an absolute path to the base directory containing atsumi and developer projects
	 * which is used as the base folder for loading external project files from
	 * @access protected
	 * @return string An absolute path to the project base directory
	 */
	protected function findWorkspace() {
		$matches = null;

		$file = str_replace('\\', '/', __FILE__);

		if(!preg_match('|^(.*)/classes/core/loader/atsumi_Loader.php$|', $file, $matches))
			throw new Exception('The atsumi loader cannot find its workspace!');

		$matches = explode('/', $matches[1]);
		$this->atsumiDir = array_pop($matches);
		return implode('/', $matches);
	}

	/**
	 * Used to load or include a set of files as specified in the spec
	 *
	 * Examples:
	 * references('atsumi', 'helpers mvc database');
	 * references('atsumi', array('helpers', 'mvc', 'database'));
	 * references(array('atsumi' => 'helpers mvc database'));
	 * @access public
	 * @param $spec mixed See above for examples
	 */
	public function _references($spec) {
		$args = func_get_args();
		switch(func_num_args()) {
			case 1:
				if(!is_array($args[0]))
					throw new Exception('Loader spec must be an associative array');
				$spec = $args[0];
				break;
			case 2:
				$spec = array($args[0] => $args[1]);
				break;
			default:
				throw new Exception('atsumi_Loader: Invalid number of args');
				break;
		}

		$domains = '';
		$collections = '';
		foreach($spec as $domain => $parts) {
			$domains .= $domain.', ';
			if(isset($this->processedPaths[$domain.':'.$parts])) continue;
			$this->processedPaths[$domain.':'.$parts] = true;

			if(!is_array($parts)) {
				$collections .= '['.$parts.']';
				$parts = explode(' ', $parts);
			} else {
				$collections .= '['.implode(', ', $parts).']';
			}

			foreach($parts as $part) {
				if (empty($part)) continue;
				$success = false;
				if($this->useRequire($domain, $part)) $success = true;
				if($this->useClasses($domain, $part)) $success = true;

				if(!$success) throw new Exception(sprintf('Unknown reference in atsumi_Loader::references() : %s:%s', $domain, $part));
			}
		}
	}

	/**
	 * Tries to find a class instance within the classes directory and add it to the autoloader
	 * @access protected
	 * @param string $domain The project domain folder to look in
	 * @param string $part The subfolder to process
	 * @return boolean If the part was found
	 */
	protected function useClasses($domain, $part) {
		$path = sprintf('%s/%s/classes/%s', $this->workspace, $domain, $part);
		if(!is_dir($path)) return false;
		self::useClassDir($path);
		return true;
	}

	/**
	 * Tries to find an include a required file in the includes directory
	 * @access protected
	 * @param string $domain The project domain folder to look in
	 * @param string $part The name of the file to look for
	 * @return boolean If the part was found
	 */
	protected function useRequire($domain, $part) {
		$path = sprintf('%s/%s/include/%s.php', $this->workspace, $domain, $part);
		if(!is_file($path)) return false;

		require_once($path);
		return true;
	}

	/**
	 * Process a directory presuming all files are named relative to the class they contain
	 * @access protected
	 * @param string $path The absolute path to the directory to process
	 */
	protected function useClassDir($path) {
		$contents = scandir($path);
		foreach($contents as $fileName) {
			if(substr($fileName, 0, 1) == '.') continue;

			$fullPath = $path.'/'.$fileName;
			
			// Recursive loading of subdirectories
			if(is_dir($fullPath)) {
				self::useClassDir($fullPath);
				continue;
			}
			if(is_file($fullPath) && preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)[\.inc\|]*.php$/', $fileName, $matches)) {
				$className = str_replace('.php', '', $matches[0]);
				$className = str_replace('.inc', '', $className);
				self::_registerClass($className, $fullPath);
			}
		}
	}

	/**
	 * Used to register a class to the auto loader
	 * @access public
	 * @param string $classname The name of the class
	 * @param string $filePath An absolute path to the file containing the class
	 */
	public function _registerClass($classname, $filePath) {
		$classname = strtolower($classname);

		if(array_key_exists($classname, $this->classes) && $filePath !== $this->classes[$classname]) {
			throw new loader_DuplicateClassException(
				'Duplicated class '.$classname.' in '.$this->classes[$classname].' and '.$filePath
			);
		}

		$this->classes[$classname] = $filePath;
	}

	/**
	 * Used by the PHP auto load function to load a class
	 * @access public
	 * @param string $classname The name of the class to load
	 */
	public function _loadClass($classname) {
		$classname = strtolower($classname);
		if(!array_key_exists($classname, $this->classes)) 
			throw new loader_ClassNotFoundException('Atsumi failed to find the class required \''.$classname.'\'', $classname);
		
		require_once($this->classes[$classname]);
	}

	/*
	 * NOTE: Below are all static functions which will be removed when php 5.3.0+ becomes the
	 * common and the PHP magic function __callStatic works correctly
	 */
	public static function getAtsumiDir() {
		return self::__callStatic(__FUNCTION__, array());
	}

	public static function getWorkspace() {
		return self::__callStatic(__FUNCTION__, array());
	}

	public static function references($spec) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public function registerClass($className, $filePath) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	static public function loadClass($className) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}
}

/**
 * Used internally by PHP to help attempt to load missing classes
 * @param $className The class to load
 */
function __autoload($classname) {
	atsumi_Loader::loadClass($classname);
}
?>