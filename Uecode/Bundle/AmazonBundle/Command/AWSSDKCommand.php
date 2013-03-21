<?php

/**
 * A symfony command to run Amazon SDK Commands
 *
 * @author John Pancoast
 * @date 2013-03-20
 */

namespace Uecode\Bundle\AmazonBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class AWSSDKCommand extends Command
{
    protected $name = "poll:ue:workflow:uePoc";
    protected function configure() {
        $this
            ->setName('ue:workflow:start')
            ->setDescription('Send a start workflow execution to amazon.')
            ->addArgument(
                'aws_key',
                InputArgument::REQUIRED,
                'The amazon AWS key used for authentication'
            )
            ->addArgument(
                'aws_secret',
                InputArgument::REQUIRED,
                'The amazon AWS secret used for authentication'
            )
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
        $key = $input->getArgument('aws_key');
        $secret = $input->getArgument('aws_secret');
        $command = $input->getArgument('sdk_command');
        $options = $input->getArgument('options');

        $swf = new AmazonSWF(array('key' => $key, 'secret' => $secret));

        if (!method_exists($swf, $command)) {
            throw new Exception('Amazon SWF/SDK method \'$command\' does not exist');
        }

        $result = $swf->{$command}(json_decode($options, true));
        $output->writeln($result->body);
    }
}
