<?php

namespace GoodIRC\IRCMessage;

use GoodIRC\IRCHostname;

class JoinMessage extends BaseMessage {

	/**
	 * Hostname
	 *
	 * @var null|IRCHostname
	 */
	private $host;

	/**
	 * Channel being joined
	 *
	 * @var string
	 */
	private $channel;

	public function __construct( $channel = '' ) {
		$this->channel = $channel;
	}

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return 'JOIN';
	}

	/**
	 * Parses a raw IRC line
	 *
	 * @param $line string
	 */
	public function parse( $line ) {
		list( $host, , $channel ) = explode( ' ', $line, 3 );

		$this->host = new IRCHostname( $host );
		$this->channel = substr( $channel, 1 );
	}

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	public function __toString() {
		return "JOIN {$this->channel}";
	}

	/**
	 * @param string $channel
	 * @return \GoodIRC\IRCMessage\JoinMessage
	 */
	public function setChannel( $channel ) {
		$this->channel = $channel;
		return $this;
	}
}
