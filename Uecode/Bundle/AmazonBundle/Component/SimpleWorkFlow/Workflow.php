<?php
/**
 * User: Aaron Scherer
 * Date: 2/13/13
 */
namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;

use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;

// Amazon Classes
use \AmazonSWF;

class Workflow extends AmazonComponent
{

	/**
	 * @var \CFResponse
	 */
	private $workflow;

	public function __construct( AmazonSWF $swf, array $workflowType )
	{

		$this->setAmazonClass( $swf );

		$this->workflow = $this->getWorkflow( $workflowType );
	}

	public function run( $taskList = null )
	{
		while( true ) {
			$opts = array(
				'taskList' => array(
					'name' => !is_null( $taskList ) ? $taskList : $this->workflow[ 'defaultTaskList' ]
				)
			);

			$response = $this->swf->poll_for_decision_task($opts);
		}
	}

	public function getWorkflow( array $workflowType )
	{
		if( !array_key_exists( 'name', $workflowType ) ) {
			throw new \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException( "Name must be included in the second argument." );
		}

		if( !array_key_exists( 'version', $workflowType ) ) {
			throw new \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException( "Version must be included in the second argument." );
		}

		if( !array_key_exists( 'defaultTaskList', $workflowType ) ) {
			throw new \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException( "Version must be included in the second argument." );
		}

		/** @var $swf AmazonSWF */
		$swf = $this->getAmazonClass();
		$swf->register_workflow_type( $workflowType );

		return $swf->describe_workflow_type( $workflowType );
	}

}
