<?php

namespace GoodIRC;

/**
 * Base functions for context classes
 */
abstract class Context {
	/**
	 * Starting state, IRC and system processes are starting
	 */
	const STATE_START = 0;

	/**
	 * State where IRC process is connecting to the server
	 */
	const STATE_CONNECTING = 1;

	/**
	 * State where the IRC process is connected
	 */
	const STATE_CONNECTED = 2;

	/**
	 * Current state
	 *
	 * @var int
	 */
	protected $state = Context::STATE_START;

	/**
	 * Changes the state
	 *
	 * @param $state
	 */
	protected function changeState( $state ) {
		$this->state = $state;
	}
}
