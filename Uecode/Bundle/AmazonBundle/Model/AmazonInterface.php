<?php
/**
 * User: Aaron Scherer
 * Date: 2/9/13
 */
namespace Uecode\Bundle\AmazonBundle\Model;

use \Uecode\Bundle\AmazonBundle\Exception\InvalidConfigurationException;

use \Uecode\Bundle\UecodeBundle\Component\Config;

interface AmazonInterface
{

	/**
	 * Initializes the current object
	 *
	 * @param Config $config
	 * @return void
	 */
	function initialize( Config $config );

	/**
	 * Initialize Configs
	 *
	 * @param Config $config
	 * @return void
	 */
	function initializeConfigs( Config $config );

	/**
	 * Validates $this->configs. Should be called within initialize
	 *
	 * @throws InvalidConfigurationException
	 * @return bool
	 */
	function validateConfigs( );

	/**
	 * @param Config $config
	 * @return void
	 */
	function setConfig( Config $config );

	/**
	 * @return Config
	 */
	function getConfig( );

	/**
	 * Should be called at the end of initialize to show that the class has been initialized.
	 *
	 * @param bool $bool
	 * @return void
	 */
	function setInitialized( $bool = true );

	/**
	 * Should return whether or not the initialize function has been ran.
	 *
	 * @return bool
	 */
	function getInitialized();
}