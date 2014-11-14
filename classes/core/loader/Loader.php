<?php
/**
 * @version		0.90
 * @package		Atsumi.Framework
 * @copyright	Copyright(C) 2013, Jonny Stirling. All rights reserved.
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

namespace Atsumi\Core;

class Loader {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * What the developer has name the atsumi build
	 * @access protected
	 * @var string
	 */
	protected $atsumiDir;

	/**
	 * What the developer has named the base Atsumi namespace
	 * @access protected
	 * @var string
	 */
	protected $atsumiNamespace = '\Atsumi';

	/**
	 * The workspace that atsumi and all related projects is held within
	 * @access protected
	 * @var string
	 */
	protected $workspace;

	/**
	 * Base project namespace. Don't want to use directories if possible
	 * @access protected
	 * @var string
	 */
	protected $projectNamespace;

	/**
	 * A list of all classes that have been processed by the loader (not loaded)
	 * @access protected
	 * @var array
	 */
	protected $classes = array();

	/**
	 * A list of all namespaces that have been processed by the loader (not loaded)
	 * @access protected
	 * @var array
	 */
	protected $namespaces = array();

	/**
	 * A list of all paths that have been processed by the loader
	 * @access protected
	 * @var array
	 */
	protected $processedPaths = array();

	/**
	 * A list of all namespaces that have been processed by the loader
	 * @access protected
	 * @var array
	 */
	protected $processedNamespaces = array();

	
	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new Loader instance
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
	 * @return Loader A singlton instance of the loader
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
	private function _getAtsumiDir() {
		return $this->atsumiDir;
	}

	/**
	 * Gets the workspace of atsumi and the all related projects
	 * @access public
	 * @return string A absolute path to the workspace
	 */
	private function _getWorkspace() {
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

		// USe sprintf. No guarantee of sf at this point.
		throw new \Exception(sprintf('Undefined call to %s::%s', get_class($instance), $name));
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

		if(!preg_match('|^(.*)/classes/core/loader/Loader.php$|', $file, $matches))
			throw new \Exception('The atsumi loader cannot find its workspace!');

		$matches = explode('/', $matches[1]);
		$this->atsumiDir = array_pop($matches);
		return implode('/', $matches);
	}

	
	/**
	 * Used to load or include a set of files as specified in the spec
	 *
	 * Examples:
	 * references('atsumi', 'utility mvc database');
	 * references('atsumi', array('utility', 'mvc', 'database'));
	 * references(array('atsumi' => 'utility mvc database'));
	 * @access public
	 * @param $spec mixed See above for examples
	 */
	public function _references($spec) {

		
		$args = func_get_args();
		switch(func_num_args()) {
			case 1:
				if(!is_array($args[0]))
					throw new \Exception('Loader spec must be an associative array');
				$spec = $args[0];
				break;
			case 2:
				if(is_string($args[0]) && is_string($args[1])) {
					$spec = array($args[0] => $args[1]);
				} else {
					$this->projectNamespace = $args[0];
					$spec = $args[1];
				}
				break;
			default:
				throw new \Exception('Loader: Invalid number of args');
				break;
		}
		$domains = '';
		foreach($spec as $domain => $parts) {

			if(isset($this->processedPaths[$domain.':'.$parts])) continue;

			$this->processedPaths[$domain.':'.$parts] = true;

			if(!is_array($parts)) {
				$parts = explode(' ', $parts);
			}

			foreach($parts as $part) {
				if (empty($part)) continue;
				$success = false;
				if($this->useRequire($domain, $part)) $success = true;
				if($this->useDir($domain, $part, 'src')) $success = true;
				if($this->useDir($domain, $part, 'classes')) $success = true;

				if(!$success) throw new \loader_ClassNotFoundException(
					sprintf('Unknown reference in Loader::references() : %s:%s', $domain, $part), $domain);
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
	protected function useDir($domain, $part, $dirName) {
		$path = sprintf('%s/%s/%s/%s', $this->workspace, $domain, $dirName, $part);
		if(!is_dir($path)) return false;
		self::useClassDir($path, $domain);
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
	protected function useClassDir($path, $domain) {
		$contents = scandir($path);
		foreach($contents as $fileName) {
			if(substr($fileName, 0, 1) == '.') continue;

			$fullPath = $path.'/'.$fileName;

			// Recursive loading of subdirectories
			if(is_dir($fullPath)) {
				self::useClassDir($fullPath, $domain);
				continue;
			}
			if(is_file($fullPath) && preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)[\.inc\|]*.php$/', $fileName, $matches)) {
				$className = str_replace('.php', '', $matches[0]);
				$className = str_replace('.inc', '', $className);
				self::_registerClass($className, $fullPath);
				self::_registerNamespace($className, $fullPath, $domain);
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

		/** DEPRECATED FOR NAMESPACES
		if(array_key_exists($classname, $this->classes) && $filePath !== $this->classes[$classname]) {
		throw new Loader\Exceptions\DuplicateClassException(
		'Duplicated class '.$classname.' in '.$this->classes[$classname].' and '.$filePath
		);
		}**/

		$this->classes[$classname] = $filePath;
	}

	/**
	 * Used to register a namespace to the auto loader
	 * @access public
	 * @param string $classname The name of the class
	 * @param string $filePath An absolute path to the file containing the class
	 */
	public function _registerNamespace($classname, $filePath, $domain) {

		// get rid of folders
		if (strpos($domain, '/')) {
			$parts = explode('/', $domain);
			$domain = array_pop($parts);
			$folder = implode('/',$parts);
		} else {
			$folder = '';
		}
		
		// Strip out the base dir
		$namespacePath = str_replace($this->_getWorkspace(), '', $filePath);
		$namespacePath = str_replace($folder, '', $namespacePath);

		// Get the dir levels as array
		$dirStruct = explode('/', trim($namespacePath, '/'));

		
		// Work out if we're in Atsumi
		$baseNamespace = array_shift($dirStruct);
		if(trim($baseNamespace, '/') == trim($this->_getAtsumiDir(), '/')) {
			$baseNamespace = $this->atsumiNamespace;
		} else if($this->projectNamespace) {
			$baseNamespace =  $this->projectNamespace;
		} 

		$baseNamespace = '\\' . $baseNamespace;


		$namespace = '';
		$i = 0;
		foreach($dirStruct as $dir) {
			
			
			// Ignore the standard classes dir
			if($dir == 'classes' || $dir == 'src') {
				continue;
			}

			// if project name is same as first dir then assume namespace
			if (++$i == 1 && strtolower($baseNamespace) == '\\' . strtolower($dir)) {
				$baseNamespace = '\\' . ucfirst($dir);
				continue;
			}
			$namespace .= '\\' . ucfirst($dir);
		}

		$namespace = $baseNamespace . $namespace;

		// Finally, trim the file extension
		$namespace = substr($namespace, 0, strrpos($namespace, '.'));

		if(array_key_exists($classname, $this->classes) && $filePath !== $this->classes[$classname]) {
			throw new \loader_DuplicateClassException(
				'Duplicated class '.$classname.' in '.$this->classes[$classname].' and '.$filePath
			);
		}

		$this->namespaces[trim($namespace, '\\')] = $filePath;
	}

	/**
	 * Used by the PHP auto load function to load a class
	 * @access public
	 * @param string $classname The name of the class to load
	 */
	public function _loadClass($classname) {
		if(strpos($classname, '\\') !== false) {
			return $this->_loadClassByNamespace($classname);
		} else {
			return $this->_loadClassByName($classname);
		}
	}

	private function _loadClassByNamespace($namespace) {
		if(!array_key_exists(trim($namespace, '\\'), $this->namespaces)) {
			return;
		}
		require_once($this->namespaces[$namespace]);
	}

	private function _loadClassByName($classname) {
		$classname = strtolower($classname);

		if(!array_key_exists($classname, $this->classes))
			return;

		//throw new loader_ClassNotFoundException('Atsumi failed to find the class required \''.$classname.'\'', $classname);

		require_once($this->classes[$classname]);
	}

	static public function loadClass($className) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

}

/**
 * register the autoloader
 */
spl_autoload_register(array('Atsumi\Core\Loader', 'loadClass'));

?>