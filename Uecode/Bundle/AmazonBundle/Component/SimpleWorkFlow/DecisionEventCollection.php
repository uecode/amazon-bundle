<?php
/**
 * A collection of decision events.
 *
 * @author John Pancoast
 * @date 2013-03-25
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decision;

class DecisionEventCollection extends \ArrayObject
{
	/**
	 * Add a decision event to the collection
	 *
	 * @param DecisionEvent $decision The decision event object
	 * @param bool $persist Does this value persist event when we clear events
	 * @param string $title The unique title for this decision event.
	 * @access public
	 */
	public function addDecisionEvent(DecisionEvent $decision, $persist = false, $title = null)
	{
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
	public function setdecisionevents(decisioneventcollection $eventcollection)
	{
		$this->exchangearray($eventCollection);
	}

	/**
	 * Clear the decision event collection
	 *
	 * @access public
	 * @todo This should take into account events that have been set to persist
	 */
	public function cleardecisionevents()
	{
		$this->exchangearray(array());
	}

	/**
	 * Get the decision event collection
	 *
	 * @access public
	 * @return DecisionEventCollection
	 */
	public function getdecisionevents()
	{
		return $this;
	}
}
