<?php
/**
 * Base logic for Amazon SWF decider
 *
 * @author Aaron Scherer, John Pancoast
 * Date: 2/13/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

// Amazon Components
use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\HistoryEventIterator;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\State\DeciderWorkerState;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\SimpleWorkFlow\InvalidEventTypeException;

// Events
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\AbstractEvent;

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
	 * @var
	 */
	public $events = array();

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

		$this->setDefaultEvents();
	}

	/********************* Core Logic *********************
	 *
	 * Core Logic for our overrode Amazon Class
	 *
	 */

	/**
	 * Run the workflow!
	 */
	final public function run()
	{
		while ( true ) {
			$response = $this->swf->poll_for_decision_task( $this->workflow );
			if ( $response->isOK() ) {
				$taskToken = (string)$response->body->taskToken;

				if ( !empty( $taskToken ) ) {
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

	/**
	 * Decider logic. Runs through each history event, and decides what to do with the event
	 *
	 * @param HistoryEventIterator $history
	 * @return array
	 */
	final private function decide( HistoryEventIterator $history )
	{
		$workflowState = DeciderWorkerState::START;
		$timerOptions = null;
		$activityOptions = null;
		$continueAsNew = null;
		$maxEventId = 0;

		foreach ( $history as $event ) {
			$this->processEvent( $event, $workflowState, $timerOptions, $activityOptions, $continueAsNew, $maxEventId );
		}

		$timerDecision = self::createDecisionOptions( 'StartTimer', $timerOptions );
		$activityDecision = self::createDecisionOptions( 'ScheduleActivityTask', $activityOptions );
		$continueAsNewDecision = self::createDecisionOptions( 'ContinueAsNewWorkflowExecution', $continueAsNew );

		if ( $workflowState === DeciderWorkerState::START ) {
			return array( $timerDecision );
		} elseif ( $workflowState === DeciderWorkerState::NOTHING_OPEN ) {
			if ( $maxEventId >= Decider::EVENT_THRESHOLD_BEFORE_NEW_GENERATION ) {
				return array( $continueAsNewDecision );
			}
			return array( $timerDecision, $activityDecision );
		}
		return array();
	}

	/**
	 * Process the given history event
	 *
	 * @param $event
	 * @param $workflowState
	 * @param $timerOptions
	 * @param $activityOptions
	 * @param $continueAsNew
	 * @param $maxEventId
	 */
	protected function processEvent( $event, &$workflowState, &$timerOptions, &$activityOptions, &$continueAsNew, &$maxEventId ) {
		$maxEventId = max( $maxEventId, intval( $event->eventId ) );

		$eventType = (string)$event->eventType;

		if( array_key_exists( $eventType, $this->events ) ) {
			$this->events[ $eventType ]->run( $event, $workflowState, $timerOptions, $activityOptions, $continueAsNew, $maxEventId );
		}
	}

	/**
	 * Creates options for a decision
	 *
	 * @param $type
	 * @param $options
	 * @return array
	 */
	public static function createDecisionOptions( $type, $options )
	{
		$key = strtolower( substr( $type, 0, 1 ) ) . substr( $type, 1 ) . 'DecisionAttributes';

		return array(
			'decisionType' => $type,
			$key => $options
		);
	}

	/**
	 * Create options array for an activity
	 *
	 * @param $input
	 * @return array
	 */
	public static function createActivityOptions( $input )
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

	/**
	 * Create options array for a timer
	 *
	 * @param $input
	 * @return array
	 */
	public static function createTimerOptions( $input )
	{
		$timerDuration = (string)$input[ Decider::TIMER_DURATION_KEY ];
		$timerOptions = array(
			'startToFireTimeout' => $timerDuration,
			'timerId' => '0'
		);

		return $timerOptions;
	}

	/*
	 */
	/**
	 * Creates options for a continue
	 *
	 * When you continue a workflow execution as a new workflow execution,
	 * the start options don't carry over, so you need to specify them again.
	 * @param $startAttributes
	 * @return array
	 */
	public static function createContinueOptions( $startAttributes )
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
	 * Finds all the Events we have defined in the AmazonBundle, and initializes them
	 */
	private function setDefaultEvents()
	{
		foreach( glob( __DIR__ . '/Event/Decider/*.php' ) as $file ) {
			$eventType = str_replace( '.php', '', $file );
			$class = "\\Uecode\\Bundle\\AmazonBundle\\Component\\SimpleWorkFlow\\Event\\Decider\\" . $eventType;
			if( class_exists( $class ) ) {
				$this->setEvent( new $class(), true );
			}
		}
	}

	/**
	 * Adds/Replaces the given event to the event array.
	 *
	 * If an unknown Event is passed, and ignoreUnknown is false, we will throw an exception
	 *
	 * @param Event\AbstractEvent $event
	 * @param bool $ignoreUnknown
	 * @throws InvalidEventTypeException
	 */
	public function setEvent( AbstractEvent $event, $ignoreUnknown = false )
	{
		// If the event isnt a valid AbstractEvent, throw an exception
		if( !( $event instanceof AbstractEvent ) ) {
			throw new InvalidEventTypeException();
		}

		// If the event isnt a valid default event, and we aren't ignoring unknowns, throw an exception
		if( !array_key_exists( $event->getEventType(), $this->events ) && !$ignoreUnknown ) {
			throw new InvalidEventTypeException();
		}

		$this->events[ $event->getEventType() ] = $event;
	}

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
}
