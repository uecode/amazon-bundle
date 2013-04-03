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

class DeciderWorker extends AmazonComponent
{
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
	 * @var array Holds events in history (can be used for task lookup)
	 *
	 * @access protected
	 */
	protected $events = array();

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

		$this->workflowOptions = $workflowType;
		$this->eventNamespace = $eventNamespace;
		$this->activityNamespace = $activityNamespace;

		$this->registerWorkflow();
		$this->registerActivities();
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
			while (true) {
				$response = $this->amazonClass->poll_for_decision_task($this->workflowOptions);
				if ($response->isOK()) {
					$taskToken = (string)$response->body->taskToken;

					if (!empty($taskToken)) {
						try {
							$decision = $this->decide(
								new HistoryEventIterator($this->getAmazonClass(), $this->workflowOptions, $response)
							);
						} catch (\Exception $e) {
							// If failed decisions are recoverable, one could drop the task and allow it to be redriven by the task timeout.
							$this->debug('Failing workflow; exception in decider: '.get_class($e).' - '.$e->getMessage()."\n".$e->getTraceAsString()."\n");
							exit;
						}

						$decisionArray = array(
							'taskToken' => $taskToken,
							'decisions' => $this->createSWFDecisionArray($decision)
						);

						$completeResponse = $this->amazonClass->respond_decision_task_completed($decisionArray);

						if ($completeResponse->isOK()) {
							$this->debug("respondDecisionTaskCompleted - ".print_r($completeResponse->body, true)."\n");
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
		$maxEventId = max($maxEventId, intval($event->eventId));

		$eventType = (string)$event->eventType;
		$eventId = (int)$event->eventId;

		// save the events for later lookups
		$this->events[$eventId] = array(
			'event_type' => $eventType,
			'activity_type' => ($eventType == 'ActivityTaskScheduled' ? (string)$event->activityTaskScheduledEventAttributes->activityType->name : null)
		);

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
	 * @access public
	 * @final
	 * @return mixed
	 * @throws InvalidConfigurationException
	 */
	final public function registerWorkflow()
	{
		if ( !array_key_exists( 'name', $this->workflowOptions ) ) {
			throw new InvalidConfigurationException( "Name must be included in the second argument." );
		}

		if ( !array_key_exists( 'version', $this->workflowOptions ) ) {
			throw new InvalidConfigurationException( "Version must be included in the second argument." );
		}

		if ( !array_key_exists( 'domain', $this->workflowOptions ) ) {
			throw new InvalidConfigurationException( "Domain must be included in the third argument." );
		}

		$response = $this->amazonClass->register_workflow_type( $this->workflowOptions );
		if (!$response->isOK() && $response->body->__type != 'com.amazonaws.swf.base.model#TypeAlreadyExistsFault') {
			$this->debug('REGISTRATION ERROR: ');
			$this->debug(''.print_r($response->body, true));
			exit;
		}

		return $this->amazonClass->describe_workflow_type( $this->workflowOptions );
	}

	/**
	 * Registers activities in this workflow
	 *
	 * @final
	 * @access protected
	 * @todo TODO check for existing activities and don't make the call unless that activity/version/domain combo is not yet registered.
	 */
	protected function registerActivities()
	{
		$av = $this->amazonClass->getActivityArray();
		$domain = $this->amazonClass->getConfig()->get('domain');
		foreach (glob($av['directory'].'/*.php') as $file)
		{
			$base = substr(basename($file), 0, -4);
			$class = $av['namespace'].'\\'.$base;
			$obj = new $class;
			if ($obj instanceof AbstractActivity) {
				$opts = array(
					'domain' => $domain,
					'name' => $base,
					'version' => $obj->getVersion(),
					'defaultTaskList' => array('name' => $av['default_task_list'])
				);

				// register type (ignoring "already exists" fault for now)
				$this->amazonClass->register_activity_type($opts);
			}
		}
	}

	public function getActivityNamespace()
	{
		return $this->activityNamespace;
	}

	public function getEvents()
	{
		return $this->events;
	}

	public function debug($str)
	{
		return $this->amazonClass->debug($str);
	}
}
