<?php
/**
 * User: Aaron Scherer
 * Date: 2/13/13
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
	 * @var \CFResponse
	 */
	private $activity;

	/**
	 * @var \Closure
	 */
	private $logic = null;

	public function __construct( AmazonSWF $swf, array $activityType )
	{

		$this->setAmazonClass( $swf );

		$this->activity = $this->getActivity( $activityType );
	}

	/********************* Core Logic *********************
	 *
	 * Core Logic for our overrode Amazon Class
	 *
	 */

	public function run( $taskList = null )
	{
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
	 * Returns the amazon swf activity Object
	 *
	 * @param array $activityType
	 * @return mixed
	 * @throws InvalidConfigurationException
	 */
	public function getActivity( array $activityType )
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

	/**
	 * @param callable $deciderLogic
	 */
	public function setDeciderLogic( \Closure $deciderLogic )
	{
		$this->deciderLogic = $deciderLogic;
	}

	/**
	 * @return callable
	 */
	public function getDeciderLogic()
	{
		return $this->deciderLogic;
	}

}
