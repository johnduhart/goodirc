<?php

namespace GoodIRC\IRCMessage;

use GoodIRC\IRCHostname;

class PrivmsgMessage extends BaseHostMessage {

	/**
	 * Where the message was sent
	 *
	 * @var string
	 */
	private $destination;

	/**
	 * Message
	 *
	 * @var string
	 */
	private $message;

	/**
	 * @param string $destination
	 * @param string $message
	 */
	public function __construct( $destination = '', $message = '' ) {
		$this->destination = $destination;
		$this->message = $message;
	}

	/**
	 * Returns the IRC name of the command		$this->host = new \GoodIRC\IRCHostname()

	 *
	 * @return string
	 */
	public function getCommandName() {
		return 'PRIVMSG';
	}

	/**
	 * Parses a raw IRC line
	 *
	 * @param $line string
	 */
	public function parse( $line ) {
		list( $host, , $destination, $message ) = explode( ' ', $line, 4 );

		$this->setHost( $host );
		$this->destination = $destination;
		$this->message = substr( $message, 1 );
	}

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	public function __toString() {
		return "PRIVMSG {$this->destination} :{$this->message}";
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getDestination() {
		return $this->destination;
	}
}
