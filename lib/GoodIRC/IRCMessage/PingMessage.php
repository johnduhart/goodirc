<?php

namespace GoodIRC\IRCMessage;

class PingMessage extends BaseMessage {

	/**
	 * Server
	 *
	 * @var string
	 */
	protected $server;

	/**
	 * Constructor
	 *
	 * @param string $server
	 */
	public function __construct( $server = '' ) {
		$this->server = $server;
	}

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return 'PING';
	}

	/**
	 * Parses a raw IRC line
	 *
	 * @param $line string
	 */
	public function parse( $line ) {
		list( , $server ) = explode( ' :', $line, 2 );
		$this->server = $server;
	}

	/**
	 * Returns the message in a raw, string form
	 *
	 * @return string
	 */
	public function __toString() {
		return "PING {$this->server}";
	}

	/**
	 * @param string $server
	 * @return \GoodIRC\IRCMessage\PingMessage
	 */
	public function setServer( $server ) {
		$this->server = $server;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getServer() {
		return $this->server;
	}

	/**
	 * @return PongMessage
	 */
	public function getPong() {
		return new PongMessage( $this->server );
	}
}
