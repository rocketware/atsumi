<?php
class app_InvalidUsageException extends atsumi_AbstractException {
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
	public function __construct($_ = null) {
		parent::__construct('Usage Error: Command malformed');
	}

	/* GET METHODS */
	public function getInstructions($contentType) {
	}
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */
}
?>
