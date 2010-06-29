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
 * Interface class the implements Observer, Observable concept for push based notification
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
interface atsumi_Observer {

	/**
	 * Used by an atsumi_Observable object to notify the observer of an event
	 * @access public
	 * @param atsumi_Observable $sender The Observable object that called the observer
	 * @param atsumi_EventArgs $args Any args related to the event
	 */
	public function notify(atsumi_Observable $sender, atsumi_EventArgs $args);
}
?>