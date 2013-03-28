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
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\AbstractHistoryEvent;

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
	 * @todo review if these are still needed.
	 */
	const WORKFLOW_NAME = "defaultWorkflow";
	const WORKFLOW_VERSION = 1.0;

	/**
	 * Key to use in the workflow input
	 * @todo review if these are still needed.
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
	 * @var string event namespace
	 */
	private $eventNamespace;

	/**
	 * @var string activity namespace
	 */
	private $activityNamespace;

	/**
	 * Builds the Workflow
	 *
	 * @param \AmazonSWF $swf
	 * @param array $workflowType
	 * @param string $eventNamespace
	 * @param string $activityNamepsace
	 *
	 * @todo TODO change this to accept the workflowType array as individual values
	 * that the class will hold as class properties (cleaner).
	 * 
	 */
	final public function __construct( AmazonSWF $swf, array $workflowType, $eventNamespace, $activityNamespace )
	{
		$this->setAmazonClass( $swf );

		$this->registerWorkflow( $workflowType );

		$this->workflowOptions = $workflowType;
		$this->eventNamespace = $eventNamespace;
		$this->activityNamespace = $activityNamespace;
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
		try {
			while ( true ) {
				$response = $this->amazonClass->poll_for_decision_task( $this->workflowOptions );
				if ( $response->isOK() ) {
					$taskToken = (string)$response->body->taskToken;

					if ( !empty( $taskToken ) ) {
						try {
							$decision = $this->decide(
								new HistoryEventIterator( $this->getAmazonClass(), $this->workflowOptions, $response )
							);
						} catch ( \Exception $e ) {
							// If failed decisions are recoverable, one could drop the task and allow it to be redriven by the task timeout.
							$this->debug('Failing workflow; exception in decider: '.get_class($e).' - '.$e->getMessage()."\n".$e->getTraceAsString()."\n");
							exit;
						}

						$decisionArray = array(
							'taskToken' => $taskToken,
							'decisions' => $this->createSWFDecisionArray($decision)
						);

						$completeResponse = $this->amazonClass->respond_decision_task_completed($decisionArray);

						if ( $completeResponse->isOK() ) {
							$this->debug("respondDecisionTaskCompleted SUCCESS\n");
						} else {
							// a real application may want to report this failure and retry
							$this->debug("RespondDecisionTaskCompleted FAIL\n");
							$this->debug("Response body: \n");
							$this->debug(print_r($completeResponse->body, true));
							$this->debug("Request JSON: \n");
							$this->debug( json_encode($decisionArray) . "\n");
						}
					} else {
						$this->debug("PollForDecisionTask received empty response\n");
					}
				} else {
					$this->debug('DECISION ERROR: ');
					$this->debug(print_r( $response->body, true ));

					sleep( 2 );
				}
			}
		} catch (Exception $e) {
			$this->debug("EXCEPTION: ".$e->getMessage());
			exit;
		}
	}

	/**
	 * Decider logic. Runs through each history event and returns a decision.
	 *
	 * @param HistoryEventIterator $history
	 * @return Decision
	 */
	final private function decide(HistoryEventIterator $history)
	{
		$maxEventId = 0;

		// we have a decision object who will be passed to each event in history
		// if they have a corresponding class. Each event class can change the state
		// of the decision by adding, removing or editiing decision events.
		$decision = new Decision;

		foreach ($history as $event) {
			$this->processEvent($decision, $event, $maxEventId);
		}

		return $decision;
	}

	/**
	 * Process the given history event
	 *
	 * @param array $event
	 * @param Decision $decision
	 * @param int $maxEventId
	 */
	protected function processEvent($decision, $event, &$maxEventId)
	{
		$maxEventId = max( $maxEventId, intval( $event->eventId ) );

		$eventType = (string)$event->eventType;

		$this->debug('- '.$eventType.' - '.json_encode((array)$event)."\n");

		$defaultEventNamespace = 'Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event';
		$defaultActivityNamespace = 'Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\Activity';

		$userClass = $this->eventNamespace.'\\'.$eventType;
		$defaultClass = $defaultEventNamespace.'\\'.$eventType;

		if (class_exists($userClass)) {
			$this->debug("    - user class: $userClass ");
			$obj = new $userClass;
			if (!($obj instanceof AbstractHistoryEvent)) {
				throw new InvalidEventTypeException; 
			}
			$obj->run($this, $decision, $event, $maxEventId);
		} elseif (class_exists($defaultClass)) {
			$this->debug("    - default class: $defaultClass ");
			$obj = new $defaultClass;
			if (!($obj instanceof AbstractHistoryEvent)) {
				throw new InvalidEventTypeException; 
			}
			$obj->run($this, $decision, $event, $maxEventId);
		} else {
			$this->debug('    - no class');
		}

		$this->debug("\n");
	}

	/**
	 * Given a decision object, create a decision array appropriate for amazon's SDK.
	 *
	 * @param Decision $decision
	 * @return array
	 */
	public static function createSWFDecisionArray(Decision $decision)
	{
		$ret = array();
		foreach ($decision->getDecisionEvents() as $e)
		{
			$title = $e->getTitle();
			$ret[] = array(
				'decisionType' => $title,
				lcfirst($title).'DecisionAttributes' => $e
			);
		}
		return $ret;
	}

	/**
	 * Registers the workflow.
	 *
	 * @param array $workflowType
	 * @return mixed
	 * @throws InvalidConfigurationException
	 */
	final public function registerWorkflow( array $workflowType )
	{
		if ( !array_key_exists( 'name', $workflowType ) ) {
			throw new InvalidConfigurationException( "Name must be included in the second argument." );
		}

		if ( !array_key_exists( 'version', $workflowType ) ) {
			throw new InvalidConfigurationException( "Version must be included in the second argument." );
		}

		if ( !array_key_exists( 'domain', $workflowType ) ) {
			throw new InvalidConfigurationException( "Domain must be included in the third argument." );
		}

		$response = $this->amazonClass->register_workflow_type( $workflowType );
		if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#TypeAlreadyExistsFault') {
			echo 'REGISTRATION ERROR: ';
			print_r( $response->body );
			exit;
		}

		return $this->amazonClass->describe_workflow_type( $workflowType );
	}

	public function debug($str)
	{
		echo $str;
	}
}
