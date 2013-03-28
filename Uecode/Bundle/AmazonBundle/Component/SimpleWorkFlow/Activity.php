<?php
/**
 * Activity worker
 *
 * @author Aaron Scherer, John Pancoast
 * @date 2/13/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

// Amazon Components
use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;

// Amazon Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidDeciderLogicException;

// Amazon Classes
use \AmazonSWF;

class Activity extends AmazonComponent
{
	/**
	 * @var string The task list this activity worker polls amazon for.
	 *
	 * @access protected
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForActivityTask.html
	 */
	protected $taskList;

	/**
	 * @var string The namespace where activity classes for this task list exist.
	 *
	 * @access protected
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForActivityTask.html
	 */
	protected $namespace;

	/**
	 * @var string A user-defined identity for this activity worker.
	 *
	 * @access protected
	 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_PollForActivityTask.html
	 */
	protected $identity;

	/**
	 * @var Activity A list of singleton instances of this class keyed by taskList.
	 *
	 * @access private
	 */
	private $instances;

	/**
	 * @var array A list of the activity classes that have registered themselves to amazon.
	 *
	 * @access private
	 */
	private $registeredActivities = array();

	/**
	 * constructor
	 *
	 * Don't instantiate this class directly. Use the getInstance() method instead.
	 *
	 * @access protected
	 * @param AmazonSWF $swf Simple workflow object
	 * @param string $taskList
	 * @param string $namespace
	 * @param string $identity
	 */
	protected function __construct(AmazonSWF $swf, $taskList, $namespace, $identity = null)
	{
		$this->setAmazonClass($swf);
		$this->taskList = $taskList;
		$this->namespace = $namespace;
		$this->identity = $identity;

		$this->registerActivities();
	}

	/**
	 * Get an instance of this class.
	 *
	 * @static
	 * @access public
	 * @param AmazonSWF $swf Simple workflow object
	 * @param string $taskList
	 * @param string $namespace
	 * @param string $identity
	 * @return self
	 */
	static public function factory(AmazonSWF $swf, $taskList, $namespace, $identity = null)
	{
		if (!$this->instances) {
			$this->instances[$taskList] = new self($swf, $taskList, $namespace, $identity);
		}
		return $this->instances;
	}

	/********************* Core Logic *********************
	 *
	 * Core Logic for our overrode Amazon Class
	 *
	 */

	public function run( $taskList = null )
	{
		echo "RUN TEST";exit;
		while( true ) {
			$opts = array(
				'taskList' => array(
					'name' => !is_null( $taskList ) ? $taskList : $this->workflow[ 'defaultTaskList' ]
				)
			);

			$response = $this->swf->poll_for_decision_task($opts);
			if ( $response->isOK( ) ) {
				$taskToken = (string) $response->body->taskToken;

				if ( !empty( $taskToken ) ) {
					$deciderResponse = $this->decide( $response );

					$return = array(
						'task'   => $taskToken,
						'result' => $deciderResponse
					);
					echo json_encode( $return );
				}
			}
			sleep( 2 );
		}
	}
	/********************* Getters and Setters *********************
	 *
	 * Functions to help initialize
	 *
	 */

	/**
	 * Registers all of the activities for this activity type.
	 *
	 * @param array $activityType
	 * @return mixed
	 * @throws InvalidConfigurationException
	 */
	public function registerActivities()
	{
		if( !array_key_exists( 'name', $activityType ) ) {
			throw new InvalidConfigurationException( "Name must be included in the second argument." );
		}

		if( !array_key_exists( 'version', $activityType ) ) {
			throw new InvalidConfigurationException( "Version must be included in the second argument." );
		}

		/** @var $swf AmazonSWF */
		$swf = $this->getAmazonClass();
		$swf->register_activity_type( $activityType );

		return $swf->describe_activity_type( $activityType );
	}
}
