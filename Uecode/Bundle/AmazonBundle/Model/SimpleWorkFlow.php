<?php
/**
 * User: Aaron Scherer
 * Date: 2/9/13
 */
namespace Uecode\Bundle\AmazonBundle\Model;

// Models
use \Uecode\Bundle\AmazonBundle\Model\AmazonInterface;

// Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Amazon Bundle Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decider;

// Uecode Bundle Components
use \Uecode\Bundle\UecodeBundle\Component\Config;

use \AmazonSWF as SWF;

class SimpleWorkFlow extends SWF implements AmazonInterface
{

	/**
	 * @var bool Defines whether or not initialize() has been ran.
	 */
	private $initialized = false;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var
	 */
	protected $workflow;

	/********************* Core Logic *********************
	 *
	 * Core Logic for our overrode Amazon Class
	 *
	 */

	/**
	 * @param string $name
	 * @param float  $version
	 * @param string $taskList
	 * @param string $workflowClass
	 *
	 * @throws InvalidClassException
	 * @return Workflow
	 */
	public function loadWorkflow( $name, $version = 1.0, $taskList, $workflowClass = null )
	{
		$workflowOptions = array(
			'name' => $name,
			'version' => (string)$version,
			'taskList' => array('name' => $taskList),
			'domain' => $this->config->get( 'domain' )
		);

		if( null === $workflowClass ) {
			return new Decider( $this ,$workflowOptions );
		} else {
			$worker = new $workflowClass( $this, $workflowOptions );
			if( !( $worker instanceof Decider ) ) {
				throw new InvalidClassException( $workflowClass );
			}

			return $worker;
		}
	}

	/********************* Initializers *********************
	 *
	 * Functions to help initialize
	 *
	 */

	/**
	 * Initializes the current object
	 *
	 * @param Config $config
	 * @return void
	 */
	public function initialize( Config $config )
	{
		if ( $this->getInitialized() ) {
			return;
		}

		$this->initializeConfigs( $config );
		$this->setInitialized();

		return;
	}

	/**
	 * Initialize Configs
	 *
	 * @param Config $config
	 * @return void
	 */
	function initializeConfigs( Config $config )
	{
		$this->setConfig( $config );
		$this->validateConfigs();
	}


	/**
	 * Validates $this->configs. Should be called within initialize
	 *
	 * @throws InvalidConfigurationException
	 * @return bool
	 */
	public function validateConfigs()
	{
		if ( !$this->config->has( 'domain' ) ) {
			throw new  InvalidConfigurationException( "Domain must be specified in this config." );
		}
	}

	/********************* Getters and Setters *********************
	 *
	 * Functions to help initialize
	 *
	 */

	/**
	 * Should be called at the end of initialize to show that the class has been initialized.
	 *
	 * @param bool $bool
	 * @return void
	 */
	public function setInitialized( $bool = true )
	{
		$this->initialized = $bool;
	}

	/**
	 * Should return whether or not the initialize function has been ran.
	 *
	 * @return bool
	 */
	public function getInitialized()
	{
		return $this->initialized;
	}

	/**
	 * @param Config $config
	 * @return void
	 */
	public function setConfig( Config $config )
	{
		$this->config = $config;
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}


}
