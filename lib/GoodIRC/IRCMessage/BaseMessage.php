<?php

namespace GoodIRC\IRCMessage;

abstract class BaseMessage {
	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	abstract public function getCommandName();

	/**
	 * Parses a raw IRC line
	 *
	 * @param $line string
	 */
	abstract public function parse( $line );

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	abstract public function __toString();
}
