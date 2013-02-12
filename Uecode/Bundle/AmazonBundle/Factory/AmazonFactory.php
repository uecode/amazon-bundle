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

// AazonBundle Exceptions
use \Uecode\Bundle\AmazonBundle\Exception\ClassNotFoundException;
use \Uecode\Bundle\AmazonBundle\Exception\InvalidClassException;

use \Uecode\Bundle\UecodeBundle\Component\Config;

class AmazonFactory implements Factory
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
            $object = new $class( $options );

            // Check to make sure its a valid Amazon object
            if ( !( $object instanceof CFRuntime ) ) {
                throw new InvalidClassException( $class );
            }

            if ( method_exists( $object, 'initialize' ) ) {
                $object->initialize();
            }

            return $object;
        }

        throw new ClassNotFoundException( $className );
    }

    public function buildConfig()
    {
    }

    /**
     * Allows for calls like AmazonFactory::SWF( $options )
     *
     * @param string $name
     * @param array $arguments
     * @return bool|\CFRuntime
     */
    public function __callStatic( $name, array $arguments = array() )
    {
        return $this->build( $name, $arguments );
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
        if ( class_exists( $class ) ) {
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
        foreach ( $this->getModelConfig()->all() as $model ) {
            if ( $className === $model[ 'amazon_class' ] ) {
                $className = $model[ 'uecode_class' ];
            }
        }

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
