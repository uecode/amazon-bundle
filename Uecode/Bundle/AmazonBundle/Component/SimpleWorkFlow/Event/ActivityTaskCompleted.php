<?php

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\Event;

use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\ActivityTask;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decision;

class ActivityTaskCompleted extends ActivityTask
{
	protected $eventType = 'ActivityTaskCompleted';
}