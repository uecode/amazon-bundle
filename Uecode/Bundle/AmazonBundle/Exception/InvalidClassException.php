<?php
/**
 * User: Aaron Scherer
 * Date: 2/9/13
 */
namespace Uecode\Bundle\AmazonBundle\Exception;

use \Exception;

class InvalidClassException extends Exception
{
    public function __construct( $className )
    {
        $message = sprintf(
            "Tried creating the `%s` object, but it is not a valid CFRuntime object",
            $className
        );

        parent::__construct( $className );
    }
}