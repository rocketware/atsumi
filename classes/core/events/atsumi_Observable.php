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
 * Abstract class the implements Observer, Observable concept for push based notification
 * @package		Atsumi.Framework
 * @subpackage	Core
 * @since		0.90
 */
abstract class atsumi_Observable {

	/**
	 * A collection of observers
	 * @access private
	 * @var array
	 */
	protected $observers = array();

	/**
	 * Used to add an observer to the observer stack
	 * @final
	 * @access public
	 * @param atsumi_Observer $observer An observer to be added to the
	 * @param string $eventType The type of event to lissen for or null for all
	 */
	final public function addObserver(atsumi_Observer $observer, $eventType = null) {
		if(!array_key_exists($eventType, $this->observers))
			$this->observers[$eventType] = array();

		$this->observers[$eventType][] = $observer;
	}

	/**
	 * Used to remove an observer
	 * @final
	 * @access public
	 * @param atsumi_Observer $observer The observer to be removed
	 * @param string $eventType
	 */
	final public function removeObserver(atsumi_Observer $observer) {
		$newObservers = array();
		foreach($this->observers as $key => $value) {
			$newObservers[$key] = array();
			foreach($value as $subKey => $storedObserver)
				if($storedObserver !== $observer)
					$newObservers[$key][$subKey] = $storedObserver;
		}
		$this->observers = $newObservers;
	}

	/**
	 * Calls an event on all observers
	 * @final
	 * @access public
	 * @param string $eventType The type of even
	 * @param atsumi_EventArgs $args An event args object with information about the event
	 */
	protected function fireEvent($eventType, atsumi_EventArgs $args = null) {
		if(!array_key_exists($eventType, $this->observers))
			return;

		foreach($this->observers[$eventType] as $observer)
			$observer->notify($eventType, $this, $args);
	}
}
?>