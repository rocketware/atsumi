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
 * Error Handler Listener which will log an error to flat file
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class listener_LogToFile implements atsumi_Observer {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * The directory to place the log files in
	 * @access private
	 * @var string
	 */
	private $logDir;
	private $filePrefix;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new listener_LogToFile instance
	 * @access public
	 * @param string $logDir The directory to place the log files in
	 */
	public function __construct($logDir, $filePrefix = '') {
		$this->logDir = $logDir;
		$this->filePrefix = $filePrefix;
	}

	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Writes the error to flat file based on the directory and date
	 * @access public
	 * @param string $dataIn The data to write to the error log
	 */
	protected function writeToLog($dataIn) {
		$filename = $this->filePrefix.($this->filePrefix?'-':'').date('Y-m-d').'.log';
		$handle = @fopen($this->logDir.$filename, 'a');
		if(!$handle)
			throw new errorHandler_ListenerException('Cannot open log file: '.$this->logDir.$filename);

		$write = fwrite($handle, $dataIn);
		if ($write === false) throw new errorHandler_ListenerException('Cannot no write log file: '.$this->logDir.$filename);
		fclose($handle);
	}

	/**
	 * Used by an atsumi_Observable object to notify the lissener of an event
	 * @access public
	 * @param atsumi_Observable $sender The Observable object that called the observer
	 * @param atsumi_EventArgs $args Any args related to the event
	 */
	public function notify(atsumi_Observable $sender, atsumi_EventArgs $args) {
		$this->writeToLog(atsumi_ErrorParser::parse($args->exception, atsumi_ErrorParser::PLAINTEXT, $args->recoverer));
	}
}
?>