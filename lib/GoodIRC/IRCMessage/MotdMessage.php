<?php

namespace GoodIRC\IRCMessage;

class MotdMessage extends BaseMessage {

	/**
	 * MOTD line
	 *
	 * @var string
	 */
	private $line;

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return '372';
	}

	/**
	 * Parses a raw IRC line
	 *
	 * @param $line string
	 * @return bool
	 */
	public function parse( $line ) {
		if ( !preg_match( "/:[^ ]+ 372 .*? :(?<line>.*)$/", $line, $match ) ) {
			return false;
		}
		$this->line = $match['line'];
	}

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	public function __toString() {
		return '';
	}
}
