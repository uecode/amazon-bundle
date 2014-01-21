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

namespace Uecode\Bundle\AmazonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class AwsCommand extends ContainerAwareCommand
{
	protected function configure() {
		$this
			->setName('ue:aws:command')
			->setDescription('Call an AWS service.')
			->addArgument(
				'aws_service',
				InputArgument::REQUIRED,
				'The amazon SDK service -  e.g., s3'
			)
			->addArgument(
				'aws_service_command',
				InputArgument::REQUIRED,
				'The amazon service command - e.g., listBuckets'
			)
			->addArgument(
				'options',
				InputArgument::OPTIONAL,
				'The service command options (as JSON object)'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$service = $input->getArgument('aws_service');
		$command = $input->getArgument('aws_service_command');
        if ( $input->getArgument('options') )
            $options = json_decode( $input->getArgument('options'), true);
        else
            $options = [ ];

		$container = $this->getApplication()->getKernel()->getContainer();

        $this->service = $swf = $container->get('uecode.amazon')
                         ->getAmazonService($service, $options);
        $output->writeln( print_r( $this->service->callSDK( $command, $options ) ) );
	}

}
