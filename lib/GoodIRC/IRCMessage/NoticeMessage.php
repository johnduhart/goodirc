<?php

namespace GoodIRC\IRCMessage;

class NoticeMessage extends BaseMessage {

	/**
	 * Host or server name the NOTICE originated from
	 *
	 * @var string
	 */
	private $origin;

	/**
	 * @var string
	 */
	private $destination;

	/**
	 * Message sent
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return 'NOTICE';
	}

	/**
	 * Parses a raw IRC line
	 *
	 * @param $line string
	 * @return bool
	 */
	public function parse( $line ) {
		if ( !preg_match( "/:(?<origin>[^ ]+) NOTICE .*? :(?<message>.*)$/", $line, $match ) ) {
			return false;
		}
		$this->origin = $match['origin'];
		$this->message = $match['message'];
	}

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	public function __toString() {
		return "NOTICE {$this->destination} :{$this->message}";
	}
}
