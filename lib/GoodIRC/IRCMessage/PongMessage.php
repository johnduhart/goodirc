<?php

namespace GoodIRC\IRCMessage;

class PongMessage extends PingMessage {

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return 'PONG';
	}

	/**
	 * Parses a raw IRC line
	 *
	 * We don't
	 *
	 * @param $line string
	 */
	public function parse( $line ) {}

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	public function __toString() {
		return "PONG {$this->server}";
	}
}
