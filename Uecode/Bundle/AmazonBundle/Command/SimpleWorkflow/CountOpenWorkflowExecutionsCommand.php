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

use Uecode\Bundle\AmazonBundle\Command\SimpleWorkflow\ListOpenWorkflowExecutionsCommand;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Amazon Classes
use \AmazonSWF;
use \CFRuntime;

/**
 * Count open workflow executions
 *
 * Shares functionality w/ ListOpenWorkflowExecutions
 */
class CountOpenWorkflowExecutionsCommand extends ListOpenWorkflowExecutionsCommand
{
	/**
	 * @var command we call
	 *
	 * Here because count_open_workflow_executions and list_open_workflow_executions are similar
	 *
	 * @access protected
	 */
	protected $command = 'count_open_workflow_executions';

	/**
	 * @var string command name
	 *
	 * Here because call_open_workflow_executions and list_open_workflow_executions are similar
	 *
	 * @access protected
	 */
	protected $name = 'ue:aws:swf:countopenworkflowexecutions';

	/**
	 * @var string command description
	 *
	 * Here because call_open_workflow_executions and list_open_workflow_executions are similar
	 *
	 * @access protected
	 */
	protected $description = 'Call CountOpenWorkflowExecutions SWF API call. --domain and --oldest_date are required.';
}
