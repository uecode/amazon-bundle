<?php

/**
 * @package amazon-bundle
 * @copyright (c) 2013 Underground Elephant
 * @author Aaron Scherer
 */

namespace Uecode\Bundle\AmazonBundle\Component\SimpleWorkFlow;


// Base Classes
use \Iterator;
// Amazon Classes
use \AmazonSWF;
use \CFRequest;
use \CFResponse;

// Exceptions
use \RuntimeException;

/*
 * When histories become long, you may need to paginate through the events
 * by making multiple service calls.
 */
class HistoryEventIterator implements Iterator
{

	/**
	 * @var \AmazonSWF
	 */
	protected $swf;

	/**
	 * @var array
	 */
	protected $events;

	/**
	 * @var int
	 */
	protected $eventIndex;

	/**
	 * @var null|string
	 */
	protected $nextPageToken;

	/**
	 * @var \CFRequest
	 */
	protected $originalPollRequest;

	/**
	 * @param \AmazonSWF $swfClient
	 * @param CFRequest $originalRequest
	 * @param CFResponse $originalResponse
	 */
	public function __construct( AmazonSWF $swfClient, $originalRequest, $originalResponse )
	{
		$this->swf = $swfClient;
		$this->events = array();
		$this->eventIndex = 0;
		$this->nextPageToken = null;
		$this->originalPollRequest = $originalRequest;

		$this->processPollResponse( $originalResponse );
	}

	/**
	 * @param CFResponse $response
	 */
	protected function processPollResponse( CFResponse $response )
	{
		if ( isset( $response->body->nextPageToken ) ) {
			$this->nextPageToken = (string)$response->body->nextPageToken;
		} else {
			$this->nextPageToken = null;
		}

		$nextEvents = $response->body->events()->getArrayCopy();
		$this->events = array_merge( $this->events, $nextEvents );
	}

	/**
	 * @throws \RuntimeException
	 */
	protected function getNextEventPage()
	{
		if ( isset( $this->nextPageToken ) ) {
			$nextPageOpts = $this->originalPollRequest;
			$nextPageOpts[ 'nextPageToken' ] = $this->nextPageToken;

			// Unfortunately, we need to retry this because you can be throttled if you have a lot of
			// pagination happening, and you want your decider to behave relatively predictably in
			// that case. A real application may want some sort of exponential backoff.
			$retry_count = 10;
			$current_retry = 1;
			$delay_between_tries = 2;
			$response = $this->swf->poll_for_decision_task( $nextPageOpts );

			while ( !$response->isOK() && $current_retry < $retry_count ) {
				sleep( $delay_between_tries );
				$response = $this->swf->poll_for_decision_task( $nextPageOpts );
				++$current_retry;
			}

			if ( !$response->isOK() ) {
				throw new RuntimeException( json_encode( $response->body ) );
			}

			$this->processPollResponse( $response );
		}
	}

	/**
	 *
	 */
	public function rewind()
	{
		$this->eventIndex = 0;
	}

	/**
	 * @return mixed
	 */
	public function current()
	{
		return $this->events[ $this->eventIndex ];
	}

	/**
	 * @return int|mixed
	 */
	public function key()
	{
		return $this->eventIndex;
	}

	/**
	 *
	 */
	public function next()
	{
		++$this->eventIndex;

		if ( $this->eventIndex >= count( $this->events ) && isset( $this->nextPageToken ) ) {
			$this->getNextEventPage();
		}
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		return $this->eventIndex < count( $this->events );
	}
}
