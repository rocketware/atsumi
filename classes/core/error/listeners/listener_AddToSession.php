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
 * Error Handler Listener which will add information to the session when an error occurs
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class listener_AddToSession implements atsumi_Observer {
	/* CONSTANTS */
	const METHOD_SET 	= 0;
	const METHOD_PUSH 	= 1;

	/* PROPERTIES */

	/**
	 * The data to add when an error occurs
	 * @access private
	 * @var mixed
	 */
	private $data;

	/**
	 * The key to add the data under
	 * @access private
	 * @var string|integer
	 */
	private $key;

	/**
	 * The namespace to add under if atsumi session is being used
	 * @access private
	 * @var string
	 */
	private $namespace;

	private $method;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new listener_AddToSession instance
	 * @access public
	 * @param string|integer $key The key to add the data under
	 * @param mixed $data The data to add when the error occurs
	 * @param string $namespace The namespace to add under if atsumi session is being used [optional, default: session_Handler::DEFAULT_NAMESPACE]
	 */
	public function __construct($key, $data, $namespace = session_Handler::DEFAULT_NAMESPACE, $method= self::METHOD_SET) {

		$this->data 				= $data;
		$this->key 					= $key;
		$this->namespace 			= $namespace;
		$this->method 				= $method;
	}

	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Adds the lissener data to the session using atsumi session
	 * @access protected
	 * @param string $errorText The text representation of the error
	 */
	protected function mergeDataToSession($errorText) {

		$session = session_Handler::getInstance();

		switch ($this->method) {
		case self::METHOD_SET:
			$session->set($this->key, $this->data, $this->namespace);
			break;
		case self::METHOD_PUSH:
			if (!$session->has($this->key, $this->namespace) || !is_array($session->get($this->key, $this->namespace)))
					$session->set($this->key, array($this->data), $this->namespace);
			else 	$session->push($this->key, $this->data, $this->namespace);
			break;
		}
	}

	/**
	 * Used by an atsumi_Observable object to notify the lissener of an event
	 * @access public
	 * @param atsumi_Observable $sender The Observable object that called the observer
	 * @param atsumi_EventArgs $args Any args related to the event
	 */
	public function notify(atsumi_Observable $sender, atsumi_EventArgs $args) {
		$this->mergeDataToSession(atsumi_ErrorParser::parse($args->exception, atsumi_ErrorParser::PLAINTEXT, $args->recoverer));
	}
}
?>