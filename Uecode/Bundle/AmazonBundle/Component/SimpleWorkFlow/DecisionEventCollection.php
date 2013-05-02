<?php
/**
 * A collection of decision events.
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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decision;

class DecisionEventCollection extends \ArrayObject
{
	/**
	 * Add a decision event to the collection
	 *
	 * @param DecisionEvent $decision The decision event object
	 * @param bool $clearEvents Do we clear events before adding this event.
	 * @param string $title The unique title for this decision event.
	 * @access public
	 *
	 */
	public function addDecisionEvent(DecisionEvent $decision, $clearEvents = false, $title = null)
	{
		if ($clearEvents) {
			$this->clearDecisionEvents();
		}
		$this->offsetSet(($title ?: $decision->getTitle()), $decision);
	}

	/**
	 * Sets the decision event collection (replacing the old collection)
	 *
	 * @param DecisionEventCollection $eventCollection The collection
	 * @access public
	 * @todo This should take into account events that have been set to persist.
	 * It will need to put persisted events into the collection.
	 */
	public function setDecisionEvents(DecisionEventCollection $eventCollection)
	{
		$this->exchangeArray($eventCollection);
	}

	/**
	 * Clear the decision event collection
	 *
	 * @access public
	 * @todo This should take into account events that have been set to persist
	 */
	public function clearDecisionEvents()
	{
		$this->exchangeArray(array());
	}

	/**
	 * Get the decision event collection
	 *
	 * @access public
	 * @return DecisionEventCollection
	 */
	public function getDecisionEvents()
	{
		return $this;
	}
}