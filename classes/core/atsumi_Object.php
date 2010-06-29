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
 * Experimental base class to give common functionality to all classes.
 * NOTE: Not currently in used by the Atsumi Framework
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_Object {
	/* CONSTANTS */

	/**
	 * Sets that a property call is a get call
	 * @var string
	 */
	const GET = '__GET__';

	/**
	 * Sets that a property call is a set call
	 * @var string
	 */
	const SET = '__SET__';

	/* PROPERTIES */
	/* CONSTRUCTOR & DESTRUCTOR */
	/* GET METHODS */

	/**
	 * Serves as a hash function for a particular type
	 * @access public
	 * @return string A hash code for the current Object
	 */
	public function getHashCode() {
		return hash('sha1', var_export($this, true));
	}

	/**
	 * Gets the Type of the current instance
	 * The Type instance that represents the exact runtime type of the current instance
	 * @access public
	 */
	public function getType() {
		return get_class($this);
	}

	/* SET METHODS */
	/* MAGIC METHODS */

	/**
	 * Used to implement the property methology used in higher level lanuages
	 * NOTE: Should be called by child if overridden
	 * @access public
	 * @param string $name The name of the property being looked for
	 */
	public function __get($name) {
		$function = 'Prop'.$name;
		if(method_exists($this, 'Prop'.$name))
			return $this->$function('__GET__');

		return $this->$name;
	}

	/**
	 * Used to implement the property methology used in higher level lanuages
	 * NOTE: Should be called by child if overridden
	 * @access public
	 * @param string $name The name of the property being set
	 * @param mixed $value The value to set the property to
	 */
	public function __set($name, $value) {
		$function = 'Prop'.$name;
		if(method_exists($this, 'Prop'.$name))
			return $this->$function($value);

		$this->$name = $value;
	}

	/* METHODS */

	/**
	 * Determines whether the specified Object is equal to the current Object
	 * @access public
	 * @param System.Object The Object to compare with the current Object
	 * @return bool True if the specified Object is equal to the current Object; otherwise, false
	 */
	public function equals(Object $obj) {
		return ($this === $obj);
	}

	/**
	 * Determines whether the specified Object instances are considered equal
	 * @access public
	 * @param System.Object The first Object to compare
	 * @param System.Object The second Object to compare
	 * @return bool True if the objects are considered the same; otherwise, false
	 */
	public static function same(Object $objA, Object $objB) {
		return ($objA === $objB);
	}

	/**
	 * Creates a shallow copy of the current Object
	 * @access public
	 * @return System.Object A shallow copy of the current Object
	 */
	protected function memberwiseClone() {
		return clone $this;
	}

	/**
	 * Determines whether the specified Object instances are the same instance
	 * @access public
	 * @param System.Object The first Object to compare
	 * @param System.Object The second Object to compare
	 * @return bool True if objA is the same instance as objB or if both are null references
	 * otherwise, false.
	 */
	public static function referenceEquals(Object $objA, Object $objB) {
		return ($objA.GetType() === $objB.GetType());
	}
}
?>