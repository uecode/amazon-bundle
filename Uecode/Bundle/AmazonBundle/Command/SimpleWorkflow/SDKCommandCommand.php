<?php

/**
 * A symfony command to run Amazon SDK Commands
 *
 * Note that this currently only supports v1 of the PHP SDK.
 *
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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class SDKCommandCommand extends ContainerAwareCommand
{
	protected function configure() {
		$this
			->setName('ue:aws:swf:sdkcommand')
			->setDescription('Call an SDK command.')
			->addArgument(
				'sdk_command',
				InputArgument::REQUIRED,
				'The amazon SDK command (v1 of SDK)'
			)
			->addArgument(
				'options',
				InputArgument::REQUIRED,
				'The amazon SWF options (as JSON object)'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$command = $input->getArgument('sdk_command');
		$options = $input->getArgument('options');

		$options = json_decode($options, true);

		$output->writeln(print_r($this->callSDKCommand($command, $options), true));
	}

	final protected function callSDKCommand($command, $options) {
		$container = $this->getApplication()->getKernel()->getContainer();

		$swf = $container
		  ->get('uecode.amazon')
		  ->getFactory('ue')
		  ->build('AmazonSWF', array('domain' => 'uePoc'), $container);

		if (!method_exists($swf, $command)) {
			throw new \Exception('Amazon SWF/SDK method "'.$command.'" does not exist');
		}

		return $swf->{$command}($options);
	}
}
