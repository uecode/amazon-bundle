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

class ActivityWorker extends AmazonComponent
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
	 * @var array A list of the activity classes that have registered themselves to amazon.
	 *
	 * @access private
	 */
	private $registeredActivities = array();

	/**
	 * constructor
	 *
	 * @access protected
	 * @param AmazonSWF $swf Simple workflow object
	 * @param string $taskList
	 * @param string $namespace
	 * @param string $identity
	 */
	public function __construct(AmazonSWF $swf, $taskList, $namespace, $identity = null)
	{
		$this->setAmazonClass($swf);
		$this->taskList = $taskList;
		$this->namespace = $namespace;
		$this->identity = $identity;

		$this->registerActivities();
	}

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