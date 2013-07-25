<?php
/**
 * @package amazon-bundle
 * @author John Pancoast
 * @date 2013-03-20
 * @copyright (c) 2013 Underground Elephant
 *
 * Copyright 2013 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Uecode\Bundle\AmazonBundle\Command\SimpleWorkflow;

use Uecode\Bundle\AmazonBundle\Command\SimpleWorkflow\SDKCommandCommand;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

/**
 * Terminate a workflow execution.
 */
class RegisterCommand extends SDKCommandCommand
{
	/*
	 * inherit
	 */
	protected function configure() {
		$this
			->setName('ue:aws:swf:register')
			->setDescription('Register domain, workflows, and activity types. This will eventually have args/options to control what gets registered but at present, all things in your config will be registered.')
			;
	}

	/*
	 * inherit
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$swf = $container->get('uecode.amazon')->getAmazonService('SimpleWorkflow', 'ue');
		$swf->registerAll();
	}
}