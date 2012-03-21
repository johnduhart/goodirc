<?php

namespace GoodIRC\IRCMessage;

class MotdStartMessage extends BaseMessage {

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return '375';
	}

	/**
	 * Parses a raw IRC line
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
		return '';
	}
}
