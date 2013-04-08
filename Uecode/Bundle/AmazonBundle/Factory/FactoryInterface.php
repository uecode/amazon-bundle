<?php

/**
 * @package amazon-bundle
 * @author Aaron Scherer
 * @copyright (c) 2013 Undeground Elephant
 */

namespace Uecode\Bundle\AmazonBundle\Factory;

interface FactoryInterface
{
	public function build( $className, array $options = array() );
}
