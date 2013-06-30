<?php

/**
 * A decision for amazon SWF
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
 *
 * Copyright 2013 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow;

class Decision
{
	/**
	 * @var DecisionEventCollection A collection of decision events
	 *
	 * These are the in essence the "deciding factors" in a decisions.
	 *
	 * @access private
	 */
	private $eventCollection;

	/**
	 * @var DecisionEventCollection A collection of persistent decision events.
	 *
	 * @access private
	 */
	private $persistentEventCollection;

	/**
	 * constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		$this->eventCollection = new DecisionEventCollection;
		$this->persistentEventCollection = new DecisionEventCollection;
	}

	/**
	 * Add a decision event to the collection
	 *
	 * @param DecisionEvent $decision The decision event object
	 * @param bool $clearEvents Do we clear events before adding this event.
	 * @param string $title The unique title for this decision event.
	 * @access public
	 */
	public function addDecisionEvent(DecisionEvent $decision, $clearEvents = false, $title = null)
	{
		$this->eventCollection->addDecisionEvent($decision, $clearEvents, $title);
	}

	/**
	 * Add a persistent decision event to the collection
	 *
	 * @param DecisionEvent $decision The decision event object
	 * @param string $title The unique title for this decision event.
	 * @access public
	 */
	public function addPersistentDecisionEvent(DecisionEvent $decision, $title = null)
	{
		$this->persistentEventCollection->addDecisionEvent($decision, false, $title);
	}

	/**
	 * Sets the decision event collection (replacing the old collection)
	 *
	 * @param DecisionEventCollection $eventCollection The collection
	 * @access public
	 */
	public function setDecisionEvents(DecisionEventCollection $eventCollection)
	{
		$this->eventCollection->setDecisionEvents($eventCollection);
	}

	/**
	 * Sets the persistent decision event collection (replacing the old collection)
	 *
	 * @param DecisionEventCollection $eventCollection The collection
	 * @access public
	 */
	public function setPersistentDecisionEvents(DecisionEventCollection $eventCollection)
	{
		$this->persistentEventCollection->setDecisionEvents($eventCollection);
	}

	/**
	 * Clear the decision event collection
	 *
	 * @access public
	 * @param bool $clearPersistent Do we clear persistent decision. Set to false since they're supposed to persist =)
	 */
	public function clearDecisionEvents($clearPersistent = false)
	{
		$this->eventCollection->clearDecisionEvents();

		if ($clearPersistent) {
			$this->clearPersistentDecisionEvents();
		}
	}

	/**
	 * Clear the persistent decision event collection
	 *
	 * @access public
	 * @param bool $clearPersistent Do we clear persistent decision. Set to false since they're supposed to persist =)
	 */
	public function clearPersistentDecisionEvents($clearPersistent = false)
	{
		$this->persistentEventCollection->clearDecisionEvents();
	}

	/**
	 * Get the decision event collection
	 *
	 * @access public
	 * @param bool $cast Do we cast event attributes into array
	 * @return DecisionEventCollection
	 */
	public function getDecisionEvents($cast = false)
	{
		return $this->eventCollection->getDecisionEvents($cast);
	}

	/**
	 * Get the decision event collection
	 *
	 * @access public
	 * @param bool $cast Do we cast event attributes into array
	 * @return DecisionEventCollection
	 */
	public function getPersistentDecisionEvents($cast = false)
	{
		return $this->persistentEventCollection->getDecisionEvents($cast);
	}

	/**
	 * Get the decision events as array
	 *
	 * @access public
	 * @param bool $includePersistent Do we include the persistent events
	 * @return array
	 */
	public function getDecisionArray($includePersistent = true)
	{
		$decisions = (array)$this->getDecisionEvents(true);

		if ($includePersistent) {
			$decisions = array_merge($decisions, (array)$this->getPersistentDecisionEvents(true));
		}

		return $decisions;
	}
}
