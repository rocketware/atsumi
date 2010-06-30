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
 * Base abstract settings class that all developer settings should extend from
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
abstract class atsumi_AbstractAppSettings {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * Used to store all the setting paires
	 * @access protected
	 * @var array
	 */
	protected $settings = array('error_404' => 'Page could not be found');

	/**
	 * Used to store setting cached objects
	 * @access protected
	 * @var array
	 */
	protected $cache = array();

	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */

	/**
	 * Returns a setting or throws an exception if it does not exist
	 * @access public
	 * @param string $idx The name of the setting
	 * @return mixed The data held in the setting
	 */
	public function get($key) {
		if(!array_key_exists($key, $this->settings))
			throw new Exception('Undefined Setting: '.$key);
		return $this->settings[$key];
	}

	/**
	 * Returns the state of the debugger
	 * @access public
	 * @return boolean Weather debug is enabled
	 */
	public function getDebug() {
		if(!array_key_exists('debug', $this->settings))
			return false;

		return $this->settings['debug'];
	}

	/**
	 * Returns all settings as an assoicative array
	 * @access public
	 * @return array All settings
	 */
	public function getSettings() {
		return $this->settings;
	}

	/* SET METHODS */

	/**
	 * Used to set a setting
	 * @access public
	 * @param string $key The name of the setting
	 * @param mixed $data The data to be stored in the setting
	 */
	public function set($key, $data) {
		$this->settings[$key] = $data;
	}

	/**
	 * Used to set an array of setting
	 * @access public
	 * @param array $settings The array of settings to set
	 */
	public function setArray ($settings) {
		$this->settings = array_merge($this->settings, $settings);
	}
	
	
	/* MAGIC METHODS */

	/**
	 * Magic function that converts and variable access calls to either return
	 * settings or cache objects. Either one of these can be accessed by appending
	 * init_ or get_.
	 * @access public
	 * @param string $name init_ or get_ followed by the variable name
	 * @return mixed Either a setting or cached object
	 */
	public function __get($name) {
		$matches = null;
		if(preg_match('/^init_(.+)$/', $name))
			return $this->initCache($name);
		if(preg_match('/^get_(.+)$/', $name, $matches))
			return call_user_func(array($this, 'get'), $matches[1]);
		throw new Exception('Undefined member Settings::'.$name);
	}

	/* METHODS */

	/**
	 * The function should return an array that specifies the site layout.
	 *
	 * The array is used by the uri parser to determine what controller should
	 * be executed in relation to the uri.
	 * @access public
	 * @return array A site specification
	 */
	abstract public function init_specification();

	/**
	 * Creates and/or returns a singleton instance of an object using init_ functions
	 * @param string $name The name of the objects class
	 * @return object A singleton object instance
	 */
	protected function &initCache($name) {
		if(!array_key_exists($name, $this->cache))
			$this->cache[$name] = $this->$name();
		return $this->cache[$name];
	}
}
?>