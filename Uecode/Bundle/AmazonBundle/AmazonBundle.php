<?php

/**
 * @package amazon-bundle
 * @author Aaron Scherer
 * @date 10/8/12
 * @copyright (c) 2013 Underground Elephant
 * @todo FIXME is this used?
 */

namespace Uecode\Bundle\AmazonBundle;

use \Symfony\Component\HttpKernel\Bundle\Bundle;

class AmazonBundle extends Bundle
{
	public function getParent()
	{
		return 'UecodeBundle';
	}
}
