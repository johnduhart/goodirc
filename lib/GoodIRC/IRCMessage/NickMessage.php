<?php

namespace GoodIRC\IRCMessage;

class NickMessage extends BaseMessage {

	/**
	 * New nick
	 *
	 * @var string
	 */
	private $nick;

	/**
	 * @param $nick
	 */
	public function __construct( $nick ) {
		$this->nick = $nick;
	}

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return 'NICK';
	}

	/**
	 * Parses a raw IRC line
	 *
	 * @param $line string
	 */
	public function parse( $line ) {
		// TODO: Implement parse() method.
	}

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	public function __toString() {
		return "NICK {$this->nick}";
	}
}
