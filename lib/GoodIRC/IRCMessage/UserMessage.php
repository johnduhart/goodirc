<?php

namespace GoodIRC\IRCMessage;

class UserMessage extends BaseMessage {

	/**
	 * @var string
	 */
	private $userName;

	/**
	 * @var string
	 */
	private $realName;

	/**
	 * @param $userName
	 * @param $realName
	 */
	public function __construct( $userName, $realName ) {
		$this->userName = $userName;
		$this->realName = $realName;
	}

	/**
	 * Returns the IRC name of the command
	 *
	 * @return string
	 */
	public function getCommandName() {
		return 'USER';
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
		// lol
		return "USER {$this->userName} lol lol :{$this->realName}";
	}
}
