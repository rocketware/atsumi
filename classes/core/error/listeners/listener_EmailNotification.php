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
 * Error Handler Listener which will email an address when an error occurs
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
class listener_EmailNotification implements atsumi_Observer {
	/* CONSTANTS */
	/* PROPERTIES */

	/**
	 * The email address of the user who will be emailed about the error
	 * @access private
	 * @var string
	 */
	private $recipientEmail;

	/**
	 * The name of the user who will be emailed about the error
	 * @access private
	 * @var string
	 */
	private $recipientName;

	/**
	 * The email address of the developer that will be sending the email
	 * @access private
	 * @var string
	 */
	private $senderEmail;

	/**
	 * The name of the developer that will be sending the email
	 * @access private
	 * @var string
	 */
	private $senderName;

	/* CONSTRUCTOR & DESTRUCTOR */

	/**
	 * Creates a new listener_EmailNotification instance
	 * @access public
	 * @param string $recipientEmail The email address of the user who will be emailed about the error
	 * @param string $recipientName The name of the user who will be emailed about the error
	 * @param string $senderEmail The email address of the developer that will be sending the email
	 * @param string $senderName The name of the developer that will be sending the email
	 */
	public function __construct($recipientEmail, $recipientName, $senderEmail, $senderName) {
		// Recipient Details
		$this->recipientEmail = $recipientEmail;
		$this->recipientName = $recipientName;

		// Sender details
		$this->senderEmail = $senderEmail;
		$this->senderName = $senderName;
	}

	/* GET METHODS */
	/* SET METHODS */
	/* MAGIC METHODS */
	/* METHODS */

	/**
	 * Sends an email with subject and body from the developer to the user
	 * @access protected
	 * @param string $subject The subject of the email
	 * @param string $body The body of the email
	 */
	protected function sendMail($subject, $body) {
		mail(
			$this->recipientEmail,
			$subject,
			$body,
			'From: '.$this->senderName.' <'.$this->senderEmail.'>'."\r\n".
			'X-Mailer: Atsumi PHP/'.phpversion().
			'MIME-Version: 1.0'."\r\n".
			'Content-Type: text/html; charset=utf-8'."\r\n".
			'Content-Transfer-Encoding: 8bit'."\r\n\r\n",
			'-f'.$this->senderEmail
		);
	}

	/**
	 * Used by an atsumi_Observable object to notify the lissener of an event
	 * @access public
	 * @param atsumi_Observable $sender The Observable object that called the observer
	 * @param atsumi_EventArgs $args Any args related to the event
	 */
	public function notify(atsumi_Observable $sender, atsumi_EventArgs $args) {
		$this->sendMail(
   			'Exception: '.$args->exception->getMessage(),
   			atsumi_ErrorParser::parse($args->exception, atsumi_ErrorParser::HTML, $args->recoverer)
   		);
	}
}
?>