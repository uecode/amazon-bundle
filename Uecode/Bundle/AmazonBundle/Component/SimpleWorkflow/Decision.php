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
	 * constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		$this->eventCollection = new DecisionEventCollection;
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
	 * Clear the decision event collection
	 *
	 * @access public
	 * @todo This should take into account events that have been set to persist
	 */
	public function clearDecisionEvents()
	{
		$this->eventCollection->clearDecisionEvents();
	}

	/**
	 * Get the decision event collection
	 *
	 * @access public
	 * @return DecisionEventCollection
	 */
	public function getDecisionEvents()
	{
		return $this->eventCollection;
	}
}
