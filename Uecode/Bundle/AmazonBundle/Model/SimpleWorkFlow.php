<?php
/**
 * User: Aaron Scherer
 * Date: 2/9/13
 */
namespace Uecode\Bundle\AmazonBundle\Model;

use \Uecode\Bundle\AmazonBundle\Model\AmazonInterface;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;

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

	/**
	 * Initializes the current object
	 *
	 * @param Config $config
	 * @return void
	 */
	public function initialize( Config $config )
	{
		if( $this->getInitialized() ) {
			return;
		}

		$this->initializeConfigs( $config );
		$this->setInitialized( );

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
	public function validateConfigs(  )
	{
		if( !$this->config->has( 'domain' ) ) {
			throw new  InvalidConfigurationException( "Domain must be specified in this config." );
		}
	}

	public function authenticate( $operation, $payload )
	{
		$payload[ 'domain' ] = $this->config->get( 'domain' );

		return parent::authenticate( $operation, $payload );
	}


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
