<?php
/**
 * @author Aaron Scherer, John Pancoast
 * @date 2/9/13
 */
namespace Uecode\Bundle\AmazonBundle\Model;

// Models
use \Uecode\Bundle\AmazonBundle\Model\AmazonInterface;

// Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Amazon Bundle Components
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\ActivityWorker;

// Uecode Bundle Components
use \Uecode\Bundle\UecodeBundle\Component\Config;

use \AmazonSWF as SWF;

/**
 * @todo this class should encapsulate swf, not extend it.
 */
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
	 * Returns a workflow defined in a config.
	 *
	 * @param string $configKey The config key for the workflow which is relative to uecode.amazon.simpleworkflow.domains.[name].workflows.
	 * @return DeciderWorker
	 */
	public function loadDeciderFromConfig($configKey)
	{
		$cfg = $this->config->get('simpleworkflow');
		foreach ($cfg['domains'] as $dk => $dv) {
			foreach ($dv['workflows'] as $kk => $kv) {
				if ($kk == $configKey) {
					return $this->loadDecider($kv['name'], $kv['version'], $kv['default_task_list'], $kv['event_namespace'], $kv['activity_namespace']);
				}
			}
		}
	}

	/**
	 * Load a decider
	 *
	 * @param string $name
	 * @param float  $version
	 * @param string $taskList
	 * @param string $eventNamespace
	 * @param string $activityNamespace
	 * @param string $deciderClass
	 *
	 * @throws InvalidClassException
	 * @return DeciderWorker
	 */
	public function loadDecider( $name, $version = 1.0, $taskList, $eventNamespace, $activityNamespace, $deciderClass = null )
	{
		$workflowOptions = array(
			'name' => $name,
			'version' => (string)$version,
			'taskList' => array('name' => $taskList),
			'domain' => $this->config->get( 'domain' )
		);

		if( null === $deciderClass ) {
			return new DeciderWorker( $this ,$workflowOptions, $eventNamespace, $activityNamespace );
		} else {
			$worker = new $deciderClass( $this, $workflowOptions, $eventNamespace, $activityNamespace );
			if( !( $worker instanceof DeciderWorker ) ) {
				throw new InvalidClassException( $deciderClass );
			}

			return $worker;
		}
	}

	public function loadActivity($taskList, $namespace, $identity = null)
	{
		return new ActivityWorker($this, $taskList, $namespace, $identity);
	}

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
