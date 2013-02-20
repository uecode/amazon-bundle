<?php
/**
 * @author Aaron Scherer
 * @date   2/19/13
 */
namespace Uecode\Bundle\AmazonBundle\Command;

// Symfony Classes
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

// Uecode Classes
use \Uecode\Bundle\DaemonBundle\Command\ExtendCommand;
use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;

abstract class PollCommand extends ExtendCommand
{

	/**
	 * Command Name
	 *
	 * Should be something like poll:ue:workflow:Name or poll:ue:activity:name
	 *
	 * @var string Command Name
	 */
	protected $name;


	/**
	 * @var AmazonComponent
	 */
	protected $worker;

	/**
	 * Daemon Logic Container
	 */
	final protected function daemonLogic()
	{

		$this->getWorker()->run();
	}

	final protected function execute( InputInterface $input, OutputInterface $output )
	{
		$this->setWorker();
		parent::execute( $input, $output );
	}


	/**
	 */
	abstract public function setWorker();

	/**
	 * @return \Uecode\Bundle\AmazonBundle\Component\AmazonComponent
	 */
	public function getWorker()
	{
		return $this->worker;
	}


}
