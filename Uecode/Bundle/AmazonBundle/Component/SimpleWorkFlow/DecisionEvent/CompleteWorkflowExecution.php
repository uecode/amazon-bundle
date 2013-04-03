<?php
/**
 * CompleteWorkflowExecution decision event
 *
 * @author John Pancoast
 * @date 2013-04-02
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_Decision.html
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_CompleteWorkflowExecutionDecisionAttributes.html
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DecisionEvent;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DecisionEvent;

class CompleteWorkflowExecution extends DecisionEvent
{
	public $result = '';
}