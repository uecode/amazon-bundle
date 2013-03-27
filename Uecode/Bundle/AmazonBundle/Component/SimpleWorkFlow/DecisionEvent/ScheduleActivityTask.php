<?php
/**
 * ScheduleActivityTask decision event
 *
 * @author John Pancoast
 * @date 2013-03-26
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_Decision.html
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_ScheduleActivityTaskDecisionAttributes.html
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DecisionEvent;

use Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow\DecisionEvent;

class ScheduleActivityTask extends DecisionEvent
{
	public $activityId = null;
	public $activityType = array('name' => 'MUST BE DEFINED BY YOU', 'version' => '1.0');
	public $control = null;
	public $heartbeatTimeout = 'NONE';
	public $input = null;
	public $scheduleToCloseTimeout = 'NONE';
	public $scheduleToStartTimeout = 'NONE';
	public $startToCloseTimeout = 'NONE';
	public $taskList = null;
}