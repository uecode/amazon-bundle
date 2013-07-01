<?php
/**
 * StartChildWorkflowExecution decision event
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DecisionEvent;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\DecisionEvent;

/**
 * StartChildWorkflowExecution decision event
 *
 * This Will define the required structure for this decision event. Optional properties
 * will be commented out which your object may or may not define.
 *
 * @author John Pancoast
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_Decision.html
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_StartChildWorkflowExecutionDecisionAttributes.html
 */
class StartChildWorkflowExecution extends DecisionEvent
{
	// optional properties will be commented out.
	// @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_StartChildWorkflowExecutionDecisionAttributes.html

	//public $childPolicy = ''; // optional
	//public $control = ''; // optional
	//public $executionStartToCloseTimeout = ''; // optional
	//public $input = ''; // optional
	//public $tagList = ''; // optional
	//public $taskList = ''; // optional
	//public $taskStartToCloseTimeout = ''; // optional
	//public $taskStartToCloseTimeout = ''; // optional
	public $workflowId = '';
	public $workflowType = array('name' => 'defined by you', 'version' => 'defined by you');
}
