<?php
/**
 * @version		0.90
 * @package		Atsumi.Framework
 * @copyright	Copyright (C) 2008, James A. Forrester-Fellowes. All rights reserved.
 * @license		GNU/GPL, see license.txt
 * The Atsumi Framework is open-source software. This version may have been modified pursuant to
 * the GNU General Public License, and as distributed it includes or is derivative of works
 * licensed under the GNU General Public License or other free or open source software licenses.
 * See copyright.txt for copyright notices and details.
 */

/**
 * Atsumi, the main framework object
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class Atsumi {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * A handle to the class loader.
	 * @access private
	 * @var atsumi_Loader
	 */
	private $loadHandler;

	/**
	 * A handle to the application handler.
	 * @access private
	 * @var atsumi_AppHandler
	 */
	private $appHandler;

	/**
	 * A handle to the error handler.
	 * @access private
	 * @var atsumi_ErrorHandler
	 */
	private $errorHandler;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new Atsumi instance
	 * Note: Private prevents anything externally creating the debugger. Use static functions.
	 * @access private
	 */
	private function __construct() {
		// Create an instance of the error handler
		$this->errorHandler = new atsumi_ErrorHandler();

		// Load some helpful files
		atsumi_Loader::references(atsumi_Loader::getAtsumiDir(), 'caster utility/http');
	}

	/* GET METHODS */

	/**
	 * Creates and/or returns a singlton instance of the class
	 * Note: Protected prevents anything externally calling this function. Use static functions.
	 * @access protected
	 * @return Atsumi A singlton instance of Atsumi
	 */
	protected static function getInstance() {
		static $instance;

		if(!is_object($instance))
			$instance = new self();

		return $instance;
	}

	/* SET METHODS */
	/* MAGIC METHODS */

	/**
	 * PHP Magic function, Disables cloning.
	 * @access private
	 */
	private function __clone() {}

	/**
	 * PHP Magic function, kills execution if the atsumi object is echoed or printed
	 * @access public
	 */
	public function __toString() { die('Cannot convert Atsumi to a string'); }

	/**
	 * PHP Magic function, used to call singlton methods as static methods
	 * @access public
	 * @param string $name The method being called
	 * @param array $arguments The arguments being passed to the method
	 */
	public static function __callStatic($name, $arguments) {
		$atsumi = self::getInstance();

		if(preg_match('/^app__(.+)$/', $name, $matches))
			return call_user_func_array(array($atsumi->appHandler, $matches[1]), $arguments);

		if(preg_match('/^error__(.+)$/', $name, $matches))
			return call_user_func_array(array($atsumi->errorHandler, $matches[1]), $arguments);

		if(method_exists($atsumi, '_'.$name))
			return call_user_func_array(array($atsumi, '_'.$name), $arguments);

		throw new Exception('Undefined call to Atsumi::'.$name);
	}

	/* METHODS */

	/**
	 * When in debug mode, atsumi will attempt to test the server enviroment to try and detect
	 * possible problems before the project goes live
	 * @access private
	 */
	private function testEnviroment() {
		if(!atsumi_Version::PhpCompatable())
			throw new atsumi_PhpVersionException(atsumi_Version::REQUIRED_PHP_VERSION);
	}

	/**
	 * Starts atsumi for the first time
	 * @access public
	 */
	public function _start() {
	}

	/**
	 * Initalises the application handler object used by Atsumi to process the developers
	 * application as well as setting up any debug related items if debug is enabled
	 * @param atsumi_AbstractAppSettings $settings
	 */
	public function _initApp(atsumi_AbstractAppSettings $settings) {
		if($settings->getDebug()) {
			atsumi_Debug::setActive(true);
			atsumi_Debug::setConfig($settings);
			$this->testEnviroment();
		} else {
			$this->errorHandler->setDisplayErrors(false);
		}

		$this->appHandler = new atsumi_AppHandler($settings, $this->errorHandler);
	}

	/*
	 * NOTE: Below are all static functions which will be removed when php 5.3.0+ becomes the
	 * common and the PHP magic function __callStatic works correctly
	 */
	public static function start() {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function initApp($settings, $debug = false) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function app__setUriParser($parser) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function app__setPath($path) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function app__go($path) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function app__render() {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function app__getParserMetaData() {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function app__createUri($controller, $method) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function error__setRecoverer($recoverer) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function error__addObserver($observer, $eventType) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function error__removeObserver($observer) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}

	public static function error__setFloodControl(cache_HandlerInterface $cacheManager, $duration) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}
	
	public static function error__listen(Exception $e) {
		$args = func_get_args();
		return self::__callStatic(__FUNCTION__, $args);
	}
	
	
}

?>