<?php
/**
 * A decision for amazon SWF
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

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
	 * @param bool $persist Does this value persist event when we clear events
	 * @param string $title The unique title for this decision event.
	 * @access public
	 */
	public function addDecisionEvent(DecisionEvent $decision, $persist = false, $title = null)
	{
		$this->eventCollection->addDecisionEvent($decision, $persist, $title);
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
