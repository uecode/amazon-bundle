<?php

/**
 * Start a decider worker.
 *
 * @author John Pancoast
 * @date 2013-03-20
 */

namespace Uecode\Bundle\AmazonBundle\Command\SimpleWorkflow;

//use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

class DeciderWorkerCommand extends ContainerAwareCommand
{
    protected function configure() {
        $this
            ->setName('ue:aws:simpleworkflow:deciderworker')
            ->setDescription('Start a decider worker which will poll amazon for a decision task.')
            ->addOption(
                'domain',
                null,
                InputOption::VALUE_REQUIRED,
                'The SWF workflow domain'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The SWF workflow name'
            )
            ->addOption(
                'tasklist',
                null,
                InputOption::VALUE_REQUIRED,
                'The SWF workflow tasklist'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getApplication()->getKernel()->getContainer();
        $domain = $input->getOptions('domain');
        $name = $input->getOptions('name');
        $version = $input->getOptions('version');
        $tasklist = $input->getOptions('tasklist');

		$amazonFactory = $container->get( 'uecode.amazon' )->getFactory( 'ue' );
		$swf = $amazonFactory->build( 'AmazonSWF', array( 'domain' => $domain ) );

		$worker = $swf->loadWorkflow( $name, $version, $tasklist );
		$worker->run();
        $output->writeln('done');
    }
}
