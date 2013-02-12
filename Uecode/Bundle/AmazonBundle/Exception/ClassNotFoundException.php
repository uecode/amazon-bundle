<?php
/**
 * User: Aaron Scherer
 * Date: 2/9/13
 */
namespace Uecode\Bundle\AmazonBundle\Exception;

use \Exception;

class ClassNotFoundException extends Exception
{
    public function __construct( $className )
    {
        $message = sprintf(
            "Tried creating the `%s` object, but it does not exist.",
            $className
        );

        parent::__construct( $className );
    }
}