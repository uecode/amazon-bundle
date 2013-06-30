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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Decision;

class DecisionEventCollection extends \ArrayObject
{
	/**
	 * Add a decision event to the collection
	 *
	 * @param DecisionEvent $decision The decision event object
	 * @param bool $clearEvents Do we clear events before adding this event.
	 * @access public
	 *
	 */
	public function addDecisionEvent(DecisionEvent $decision, $clearEvents = false)
	{
		if ($clearEvents) {
			$this->clearDecisionEvents();
		}

		$title = $decision->getTitle();

		$this->offsetSet(count($this), array(
			'decisionType' => $title,
			lcfirst($title).'DecisionAttributes' => $decision
		));
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
	 * @param bool $cast Do we cast event attributes into array
	 * @return DecisionEventCollection
	 */
	public function getDecisionEvents($cast = false)
	{
		if (!$cast) {
			return $this;
		}

		// cast the event attrs to an array

		$clone = clone $this;

		for ($i = 0, $c = count($clone); $i < $c; ++$i) {
			$event = $clone->offsetGet($i);
			$title = $event['decisionType'];
			$attr = $event[lcfirst($title).'DecisionAttributes'];

			$clone->offsetSet($i, array(
				'decisionType' => $title,
				lcfirst($title).'DecisionAttributes' => (array)$attr
			));
		}

		return $clone;
	}
}
