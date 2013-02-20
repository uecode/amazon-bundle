<?php
/**
 * User: Aaron Scherer
 * Date: 2/13/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

// Amazon Components
use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\HistoryEventIterator;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\States\DeciderWorkerStates;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidDeciderLogicException;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class Decider extends AmazonComponent
{

	/**
	 * If you increase this value, you should also
	 * increase your workflow execution timeout accordingly so that a
	 * new generation is started before the workflow times out.
	 */
	const EVENT_THRESHOLD_BEFORE_NEW_GENERATION = 150;

	/**
	 *  Workflow Constants
	 */
	const WORKFLOW_NAME = "defaultWorkflow";
	const WORKFLOW_VERSION = 1.0;

	/**
	 * Key to use in the workflow input
	 */
	const ACTIVITY_NAME_KEY = 'activityName';
	const ACTIVITY_VERSION_KEY = 'activityVersion';
	const ACTIVITY_TASK_LIST_KEY = 'activityTaskList';
	const ACTIVITY_INPUT_KEY = 'activityInput';
	const TIMER_DURATION_KEY = 'timerDuration';

	/**
	 * @var \CFResponse
	 */
	private $workflow;

	/**
	 * @var array
	 */
	private $workflowOptions = array();

	/**
	 * Builds the Workflow
	 *
	 * @param \AmazonSWF $swf
	 * @param array $workflowType
	 */
	final public function __construct( AmazonSWF $swf, array $workflowType )
	{

		$this->setAmazonClass( $swf );

		$this->workflowOptions = $workflowType;
		$this->workflow = $this->setWorkflow( $workflowType );
	}

	/********************* Core Logic *********************
	 *
	 * Core Logic for our overrode Amazon Class
	 *
	 */

	final public function run()
	{
		while ( true ) {
			$response = $this->swf->poll_for_decision_task( $this->workflow );
			if ( $response->isOK() ) {
				$taskToken = (string)$response->body->taskToken;

				if ( !empty( $taskToken ) ) {
					$deciderResponse = $this->decide( $response );
					try {
						$deciderResponse = $this->decide(
							new HistoryEventIterator( $this->getAmazonClass(), $this->workflow, $response )
						);
					} catch ( \Exception $e ) {
						// If failed decisions are recoverable, one could drop the task and allow it to be redriven by the task timeout.
						echo 'Failing workflow; exception in decider: ', $e->getMessage(), "\n", $e->getTraceAsString(
						), "\n";
					}

					$completeOpt = array(
						'task' => $taskToken,
						'result' => $deciderResponse
					);

					$complete_response = $this->swf->respond_decision_task_completed( $completeOpt );

					if ( $complete_response->isOK() ) {
						echo "RespondDecisionTaskCompleted SUCCESS\n";
					} else {
						// a real application may want to report this failure and retry
						echo "RespondDecisionTaskCompleted FAIL\n";
						echo "Response body: \n";
						print_r( $complete_response->body );
						echo "Request JSON: \n";
						echo json_encode( $completeOpt ) . "\n";
					}
				} else {
					echo "PollForDecisionTask received empty response\n";
				}
			} else {
				echo 'ERROR: ';
				print_r( $response->body );

				sleep( 2 );
			}
		}
	}

	final private function decide( HistoryEventIterator $history )
	{
		$workflowState = DeciderWorkerStates::START;
		$timerOptions = null;
		$activityOptions = null;
		$continueAsNew = null;
		$maxEventId = 0;

		foreach ( $history as $event ) {
			$this->processEvent( $event, $workflowState, $timerOptions, $activityOptions, $continueAsNew, $maxEventId );
		}

		$timerDecision = $this->createDecisionOptions( 'StartTimer', $timerOptions );
		$activityDecision = $this->createDecisionOptions( 'ScheduleActivityTask', $activityOptions );
		$continueAsNewDecision = $this->createDecisionOptions( 'ContinueAsNewWorkflowExecution', $continueAsNew );

		if ( $workflowState === DeciderWorkerStates::START ) {
			return array( $timerDecision );
		} elseif ( $workflowState === DeciderWorkerStates::NOTHING_OPEN ) {
			if ( $maxEventId >= Decider::EVENT_THRESHOLD_BEFORE_NEW_GENERATION ) {
				return array( $continueAsNewDecision );
			}
			return array( $timerDecision, $activityDecision );
		}
		return array();
	}

	protected function processEvent( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId ) {
		$maxEventId = max( $maxEventId, intval( $event->eventId ) );

		switch ( (string)$event->eventType ) {
			case 'TimerStarted':
				if ( $workflowState === DeciderWorkerStates::NOTHING_OPEN || $workflowState === DeciderWorkerStates::START ) {
					$workflowState = DeciderWorkerStates::TIMER_OPEN;
				} else {
					if ( $workflowState === DeciderWorkerStates::ACTIVITY_OPEN ) {
						$workflowState = DeciderWorkerStates::TIMER_AND_ACTIVITY_OPEN;
					}
				}
				break;
			case 'TimerFired':
				if ( $workflowState === DeciderWorkerStates::TIMER_OPEN ) {
					$workflowState = DeciderWorkerStates::NOTHING_OPEN;
				} else if ( $workflowState === DeciderWorkerStates::TIMER_AND_ACTIVITY_OPEN ) {
					$workflowState = DeciderWorkerStates::ACTIVITY_OPEN;
				}
				break;
			case 'ActivityTaskScheduled':
				if ( $workflowState === DeciderWorkerStates::NOTHING_OPEN ) {
					$workflowState = DeciderWorkerStates::ACTIVITY_OPEN;
				} else if ( $workflowState === DeciderWorkerStates::TIMER_OPEN ) {
					$workflowState = DeciderWorkerStates::TIMER_AND_ACTIVITY_OPEN;
				}
				break;
			case 'ActivityTaskCanceled':
				// add cancellation handling here
			case 'ActivityTaskFailed':
				// add failure handling here
				// when an activity fails, a real application may want to retry it or report the incident
			case 'ActivityTaskTimedOut':
				// add timeout handling here
				// when an activity times out, a real application may want to retry it or report the incident
			case 'ActivityTaskCompleted':
				if ( $workflowState === DeciderWorkerStates::ACTIVITY_OPEN ) {
					$workflowState = DeciderWorkerStates::NOTHING_OPEN;
				} else if ( $workflowState === DeciderWorkerStates::TIMER_AND_ACTIVITY_OPEN ) {
					$workflowState = DeciderWorkerStates::TIMER_OPEN;
				}
				break;
			// This is the only case which doesn't only transition state;
			// it also gathers the user's workflow input.
			case 'WorkflowExecutionStarted':
				$workflowState = DeciderWorkerStates::START;

				// gather gather gather
				$eventAttributes = $event->workflowExecutionStartedEventAttributes;
				$workflowInput = json_decode( $eventAttributes->input, true );

				$activityOptions = $this->createActivityOptions( $workflowInput );
				$timerOptions = $this->createActivityOptions( $workflowInput );
				$continueAsNew = $this->createContinueOptions( $eventAttributes );
				break;
		}
	}

	protected function createDecisionOptions( $type, $options )
	{
		$key = strtolower( substr( $type, 0, 1 ) ) . substr( $type, 1 ) . 'DecisionAttributes';

		return array(
			'decisionType' => $type,
			$key => $options
		);
	}

	protected function createActivityOptions( $input )
	{
		$activityName = $input[ Decider::ACTIVITY_NAME_KEY ];
		$activityVersion = $input[ Decider::ACTIVITY_VERSION_KEY ];
		$activityTaskList = $input[ Decider::ACTIVITY_TASK_LIST_KEY ];
		$activityInput = $input[ Decider::ACTIVITY_INPUT_KEY ];

		$activity_opts = array(
			'activityType' => array(
				'name' => $activityName,
				'version' => $activityVersion
			),
			'activityId' => 'myActivityId-' . time(),
			'input' => $activityInput,
			// This is what specifying a task list at scheduling time looks like.
			// You can also register a type with a default task list and not specify one at scheduling time.
			// The value provided at scheduling time always takes precedence.
			'taskList' => array( 'name' => $activityTaskList ),
			// This is what specifying timeouts at scheduling time looks like.
			// You can also register types with default timeouts and not specify them at scheduling time.
			// The value provided at scheduling time always takes precedence.
			'scheduleToCloseTimeout' => '30',
			'scheduleToStartTimeout' => '10',
			'startToCloseTimeout' => '60',
			'heartbeatTimeout' => 'NONE'
		);

		return $activity_opts;
	}

	protected static function createTimerOptions( $input )
	{
		$timerDuration = (string)$input[ Decider::TIMER_DURATION_KEY ];
		$timerOptions = array(
			'startToFireTimeout' => $timerDuration,
			'timerId' => '0'
		);

		return $timerOptions;
	}

	/*
	 * When you continue a workflow execution as a new workflow execution,
	 * the start options don't carry over, so you need to specify them again.
	 */
	protected static function createContinueOptions( $startAttributes )
	{
		$continueAsNewOptions = array(
			'childPolicy' => (string)$startAttributes->childPolicy,
			'input' => (string)$startAttributes->input,
			'workflowTypeVersion' => (string)$startAttributes->workflowType->version,
			// This is what specifying a task list at scheduling time looks like.
			// You can also register a type with a default task list and not specify one at scheduling time.
			// The value provided at scheduling time always takes precedence.
			'taskList' => array( 'name' => (string)$startAttributes->taskList->name ),
			// This is what specifying timeouts at scheduling time looks like.
			// You can also register types with default timeouts and not specify them at scheduling time.
			// The value provided at scheduling time always takes precedence.
			'executionStartToCloseTimeout' => (string)$startAttributes->executionStartToCloseTimeout,
			'taskStartToCloseTimeout' => (string)$startAttributes->taskStartToCloseTimeout
		);

		return $continueAsNewOptions;
	}


	/********************* Getters and Setters *********************
	 *
	 * Functions to help initialize
	 *
	 */

	/**
	 * Returns the amazon swf workflow Object
	 *
	 * @param array $workflowType
	 * @return mixed
	 * @throws InvalidConfigurationException
	 */
	final public function setWorkflow( array $workflowType )
	{
		if ( !array_key_exists( 'name', $workflowType ) ) {
			throw new InvalidConfigurationException( "Name must be included in the second argument." );
		}

		if ( !array_key_exists( 'version', $workflowType ) ) {
			throw new InvalidConfigurationException( "Version must be included in the second argument." );
		}

		if ( !array_key_exists( 'defaultTaskList', $workflowType ) ) {
			throw new InvalidConfigurationException( "Version must be included in the second argument." );
		}

		/** @var $swf AmazonSWF */
		$swf = $this->getAmazonClass();
		$swf->register_workflow_type( $workflowType );

		return $swf->describe_workflow_type( $workflowType );
	}

	/**
	 * @param callable $deciderLogic
	 */
	final public function setDeciderLogic( \Closure $deciderLogic )
	{
		$this->deciderLogic = $deciderLogic;
	}

	/**
	 * @return callable
	 */
	final public function getDeciderLogic()
	{
		return $this->deciderLogic;
	}

}
