<?php

/**
 * @package amazon-bundle
 * @author Aaron Scherer
 * @copyright (2) 2013 Undeground Elephant
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
