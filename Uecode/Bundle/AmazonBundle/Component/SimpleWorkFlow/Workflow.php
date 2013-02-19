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

class Workflow extends AmazonComponent
{

	/**
	 * @var \CFResponse
	 */
	private $workflow;

	/**
	 * @var \Closure
	 */
	private $deciderLogic = null;

	/**
	 * Builds the Workflow
	 *
	 * @param \AmazonSWF $swf
	 * @param array $workflowType
	 */
	public function __construct( AmazonSWF $swf, array $workflowType )
	{

		$this->setAmazonClass( $swf );

		$this->workflow = $this->getWorkflow( $workflowType );
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

	protected function decide( $response )
	{
		$function = $this->getDeciderLogic();
		if( null !== $function || !is_callable( $function ) ) {
			return $function( $this, $response );
		}

		throw new InvalidDeciderLogicException();
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
	public function getWorkflow( array $workflowType )
	{
		if( !array_key_exists( 'name', $workflowType ) ) {
			throw new InvalidConfigurationException( "Name must be included in the second argument." );
		}

		if( !array_key_exists( 'version', $workflowType ) ) {
			throw new InvalidConfigurationException( "Version must be included in the second argument." );
		}

		if( !array_key_exists( 'defaultTaskList', $workflowType ) ) {
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
