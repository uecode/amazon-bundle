<?php
/**
 * User: Aaron Scherer
 * Date: 2/8/13
 */
namespace Uecode\Bundle\AmazonBundle\Factory;

// Amazon Classes
use CFRuntime;

// Symfony classes
use Symfony\Component\Yaml\Yaml;

// AmazonBundle Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\ClassNotFoundException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

// Uecode Components
use \Uecode\Bundle\UecodeBundle\Component\Config;

// Uecode Amazon Classes
use Uecode\Bundle\AmazonBundle\Model\AmazonInterface;

class AmazonFactory implements FactoryInterface
{

	/**
	 * @var Config Config for models
	 */
	private $modelConfig;

	/**
	 * @var Config Config for account
	 */
	private $accountConfig;

	public function __construct( Config $accountConfig )
	{
		$this->setAccountConfig( $accountConfig );

		$file = __DIR__ . '/../Resources/config/models.yml';
		$this->setModelConfig( new Config( Yaml::parse( $file ) ) );
	}


	/**
	 * Searches for the amazon class for the given class name and builds it.
	 * If there is a Uecode Extension for this, loads that instead and initializes.
	 *
	 * @param string $className Amazon Class name
	 * @param array $options Arguments for the Amazon Class
	 * @return CFRuntime|bool Returns the amazon class
	 * @throws ClassNotFoundException|InvalidClassException
	 */
	public function build( $className, array $options = array() )
	{
		$class = $this->checkAmazonClass( $className );
		if ( $class !== false ) {

			/** @var $config array Merge given configs with account configs */
			$this->accountConfig->setItems( $options );

			$object = new $class( $this->accountConfig->all() );

			// Check to make sure its a valid Amazon object
			if ( !( $object instanceof CFRuntime ) ) {
				throw new InvalidClassException( $class );
			}

			// @TODO Add check for Amazon API v2

			if( $object instanceof AmazonInterface ) {
				$object->initialize( $this->accountConfig );
			}

			return $object;
		}

		throw new ClassNotFoundException( $className );
	}

	/**
	 * Checks to see if the Amazon Class given exists.
	 * Testing if it has Amazon in the name as well
	 *
	 * @param string $class
	 * @param bool $checkAmazon
	 * @return bool|string
	 */
	public function checkAmazonClass( $class, $checkAmazon = true )
	{
		// Check to see if we have the Amazon class
		if ( class_exists( $class ) ) {

			// If we do, check to see if we have a Uecode version of this class and replace $class with that
			$this->checkUecodeClass( $class );

			return $class;
		}

		// If $class doesnt exist, check for Amazon$class
		if ( $checkAmazon ) {
			return $this->checkAmazonClass( 'Amazon' . $class, false );
		}

		return false;
	}

	/**
	 * Checks to see if there is a Uecode extension of the given class
	 * If there is renames $className
	 *
	 * @param string $className
	 * @throws InvalidClassException
	 */
	public function checkUecodeClass( &$className )
	{

		// Run through the configs
		foreach ( $this->getModelConfig()->all() as $model ) {

			// If we have an override for the amazon class, replace it
			if ( $className === $model[ 'amazon_class' ] ) {
				$className = $model[ 'uecode_class' ];
			}
		}

		// If the given class doesn't exist, throw an error.
		// This shouldn't happen
		if ( !class_exists( $className ) ) {
			throw new InvalidClassException( $className );
		}
	}

	/**
	 * @param Config $accountConfig
	 * @return AmazonFactory
	 */
	public function setAccountConfig( Config $accountConfig )
	{
		$this->accountConfig = $accountConfig;

		return $this;
	}

	/**
	 * @return \Uecode\Bundle\UecodeBundle\Component\Config
	 */
	public function getAccountConfig()
	{
		return $this->accountConfig;
	}

	/**
	 * @param Config $modelConfig
	 * @return AmazonFactory
	 */
	public function setModelConfig( Config $modelConfig )
	{
		$this->modelConfig = $modelConfig;

		return $this;
	}

	/**
	 * @return \Uecode\Bundle\UecodeBundle\Component\Config
	 */
	public function getModelConfig()
	{
		return $this->modelConfig;
	}
}
