<?php

/**
 * ActivityTaskCompleted event.
 *
 * Note that this is one of the ActivityTask* tasks and extends ActivityTask.
 *
 * @package amazon-bundle
 * @copyright Underground Elephant
 * @author John Pancoast
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\HistoryEvent;

use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Event\ActivityTask;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DeciderWorker;
use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\Decision;

/**
 * ActivityTaskCompleted event.
 *
 * Note that this is one of the ActivityTask* tasks and extends ActivityTask.
 *
 * @package amazon-bundle
 * @copyright Underground Elephant
 * @author John Pancoast
 */
class ActivityTaskCompleted extends ActivityTask
{
	protected $eventType = 'ActivityTaskCompleted';
}
