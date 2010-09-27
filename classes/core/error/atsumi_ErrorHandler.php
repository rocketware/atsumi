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
 * Atsumi's custom error handler for dealing with errors and exceptions
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class atsumi_ErrorHandler extends atsumi_Observable {
	/* CONSTANTS */

	const EVENT_EXCEPTION 		= 'exception';		// exception
	const EVENT_EXCEPTION_FC 	= 'exceptionFC';	// exception (flood controlled)

	/* PROPERTIES */

	/**
	 * The recoverer which will be used to recover from any errors
	 * @access private
	 * @var null|object
	 */
	private $recoverer = null;

	/**
	 * A cache handler object that will stop repeated errors overloading listeners
	 * @access private
	 * @var null|cache_HandlerInterface
	 */
	private $cacheHandler = null;

	/**
	 * The duration to wait between repeat errors before calling listeners again
	 * @access private
	 * @var integer
	 */
	private $floodControlDuration = 0;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new atsumi_ErrorHandler instance
	 * @access public
	 * @param integer $errorLevel The error levels the error handler should manage
	 */
	public function __construct($errorLevel = E_ALL) {
		$this->setErrorReporting($errorLevel);

		set_error_handler(array($this, 'handleError'));
		set_exception_handler(array($this, 'handleException'));

		if(is_null($this->recoverer))
			$this->recoverer = new recoverer_DisplayAndExit();
	}

	/* GET METHODS */
	/* SET METHODS */

	/**
	 * Sets the level of error reporting that the handler should deal with
	 * @access public
	 * @param integer $errorLevel he error levels the error handler should manage
	 */
	public function setErrorReporting($errorLevel = E_ALL) {
		error_reporting($errorLevel);
	}

	/**
	 * Sets weather or not php should display none atsumi handled errors
	 * @access public
	 * @param boolean $display Weather or not to display the errors
	 */
	public function setDisplayErrors($display = false) {
		ini_set('display_errors', $display);
	}

	/**
	 * Sets the recoverer that the error handler should use to recover from errors
	 * @access public
	 * @param recoverer_Interface $recoverer The recover to use
	 */
	public function setRecoverer($recoverer) {
		if(!in_array('recoverer_Interface', class_implements($recoverer)))
			$this->handleException(new Exception('Recoverer must implement recoverer_Interface'));

		$this->recoverer = $recoverer;
	}

	/**
	 * Activates flood control, sets the cache handler and duration the cache should last
	 * @access public
	 * @param cache_HandlerInterface $cacheHandler An instance of the cache handler to use
	 * @param integer $duration The amount of time an error should be cached for
	 */
	public function setFloodControl(cache_HandlerInterface $cacheHandler, $duration) {
		$this->cacheHandler 			= $cacheHandler;
		$this->floodControlDuration 	= $duration;
	}

	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Creates a flood control key for an error, used to determine if it is a repeat error
	 * @access protected
	 * @param integer $type Weather the error was an error or exception
	 * @param integer $line The line number the error occured on
	 * @param string $file The file that the error occured in
	 * @return string A flood control key
	 */
	protected static function generateFloodControlKey($type, $line, $file) {
		return (sf('%s_%s_%s', $type, $line, $file));
	}

	/**
	 * Check weather the current error has already occured and blocked by the cache handler
	 * @access private
	 * @param integer $type Weather the error was an error or exception
	 * @param integer $line The line number the error occured on
	 * @param string $file The file that the error occured in
	 * @return boolean Weather or not the error is blocked by the flood control
	 */
	private function blockedByFloodControl($type, $line, $file) {
		// If not specified cache handler
		if(is_null($this->cacheHandler) || !$this->floodControlDuration)
			return false;

		$key = self::generateFloodControlKey($type, $line, $file);
		try {
			if($this->cacheHandler->get($key, false, 'errorHandler'))
					return true;
			else 	return false;
		} catch(cache_NotFoundException $e) {
			return false;
		}
	}

	/**
	 * Records an error in the flood control to prevent it been called repeatedly over a short time
	 * @access private
	 * @param integer $type Weather the error was an error or exception
	 * @param integer $line The line number the error occured on
	 * @param string $file The file that the error occured in
	 */
	private function recordInFloodControl($type, $line, $file) {
		if(is_null($this->cacheHandler) || !$this->floodControlDuration)
			return;

		$key = self::generateFloodControlKey($type, $line, $file);
		$this->cacheHandler->set($key, true, $this->floodControlDuration, 'errorHandler');
	}

	/**
	 * Error handling function called by PHP when an error occures
	 * @access public
	 * @param integer $errNumber The error level
	 * @param string $errString A string representation of the error
	 * @param string $errFile The file that the error occured in
	 * @param integer $errLine The line that the error occured on
	 * @param mixed $errContext The content in which the error occured
	 */
	public function handleError($errNumber, $errString, $errFile, $errLine, $errContext) {
		// Check the user has set that they want atsumi to deal with the error
		if(!((bool)($errNumber & ini_get('error_reporting'))))
			return false;

		// Convert the error into an exception and deal with everything as an exception
		throw new ErrorException($errString, 0, $errNumber, $errFile, $errLine);
	}

	/**
	 * Exception handling function called by PHP when an error occures
	 * @access public
	 * @param Exception $e The exception that was thrown
	 */
	public function handleException(Exception $e) {
		
		try {
			// fires the exception_fc event if not blocked by flood control
			if(!$this->blockedByFloodControl(get_class($e), $e->getLine(), $e->getFile()))
				$this->fireEvent(self::EVENT_EXCEPTION_FC, new atsumi_ErrorEventArgs($e, $this->recoverer));

			// fire the exception event regardless of flood control
			$this->fireEvent(self::EVENT_EXCEPTION, new atsumi_ErrorEventArgs($e, $this->recoverer));

			$this->recoverer->recover($e);
		} catch(Exception $e) {
			exit(__CLASS__.' Error: '.$e->getMessage()."\n".$e->getFile().' #'.$e->getLine(). PHP_EOL.$e->getTraceAsString());
		}
		exit();
	}

	/**
	 * Function to execute all listeners informing them of an error
	 * @access public
	 * @param Exception $e The exception that was thrown
	 * @param recoverer_Interface $recoverer The recoverer used to deal with the exception
	 */
	protected function fireEvent($eventType, atsumi_EventArgs $args = null) {
		if(!array_key_exists($eventType, $this->observers))
			return;

		foreach($this->observers[$eventType] as $listener) {
			try {
				$listener->notify($this, $args);
			} catch(errorHandler_ListenerException $listenerException) {
				// remove failed listener
				$this->removeObserver($listener);
				// handle the exception
				$this->handleException(new errorHandler_Exception(sf("Listener '%s' failed: ", get_class($listener), $listenerException->getMessage())));
			}
		}

		// record exception as listened to
		if ($eventType == self::EVENT_EXCEPTION_FC)
			$this->recordInFloodControl(get_class($args->exception), $args->exception->getLine(), $args->exception->getFile());
	}
}
?>