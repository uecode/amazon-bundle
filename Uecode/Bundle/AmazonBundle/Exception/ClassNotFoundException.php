<?php

/**
 * @package amazon-bundle
 * @author Aaron Scherer
 * @copyright (2) 2013 Undeground Elephant
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
