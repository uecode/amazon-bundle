<?php
/**
 * User: Aaron Scherer
 * Date: 2/8/13
 */
namespace Uecode\Bundle\AmazonBundle\Factory;

interface FactoryInterface
{
	public function build( $className, array $options = array() );
}
