<?php
/**
 * @author Aaron Scherer
 * @date 10/8/12
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
