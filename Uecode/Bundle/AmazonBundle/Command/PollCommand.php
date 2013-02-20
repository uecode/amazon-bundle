<?php
/**
 * @author Aaron Scherer
 * @date   2/19/13
 */
namespace Uecode\Bundle\AmazonBundle\Command;

use \Uecode\Bundle\DaemonBundle\Command\ExtendCommand;

use \Uecode\Bundle\AmazonBundle\Component\AmazonComponent;

class PollCommand extends ExtendCommand
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

	/**
	 * @param \Uecode\Bundle\AmazonBundle\Component\AmazonComponent $worker
	 */
	public function setWorker( $worker )
	{
		$this->worker = $worker;
	}

	/**
	 * @return \Uecode\Bundle\AmazonBundle\Component\AmazonComponent
	 */
	public function getWorker()
	{
		return $this->worker;
	}


}
