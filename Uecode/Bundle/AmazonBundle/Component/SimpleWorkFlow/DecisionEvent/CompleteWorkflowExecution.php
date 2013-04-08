<?php

/**
 * CompleteWorkflowExecution decision event
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_Decision.html
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_CompleteWorkflowExecutionDecisionAttributes.html
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DecisionEvent;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DecisionEvent;

class CompleteWorkflowExecution extends DecisionEvent
{
	public $result = '';
}
