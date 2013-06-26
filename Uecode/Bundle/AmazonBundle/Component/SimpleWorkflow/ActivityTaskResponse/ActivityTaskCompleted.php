<?php

/**
 * Activity task failed response
 *
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author John Pancoast
 * @see http://docs.aws.amazon.com/amazonswf/latest/apireference/API_RespondActivityTaskCompleted.html
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

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityTaskResponse;

use \Uecode\Bundle\AmazonBundle\Component\SimpleWorkflow\ActivityTaskResponse;

class ActivityTaskCompleted extends ActivityTaskResponse
{
	public $result;
	public $taskToken;
}
