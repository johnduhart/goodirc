<?php

namespace GoodIRC;

class IRCHostname {
	/**
	 * Nickname
	 *
	 * @var string
	 */
	private $nick;

	/**
	 * Username
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Hostname
	 *
	 * @var string
	 */
	private $hostname;

	public function __construct( $hostname ) {
		preg_match( '/(?<nick>.+)!(?<user>.+)@(?<host>.+)/', $hostname, $matches );
		$this->nick = $matches['nick'];
		$this->username = $matches['user'];
		$this->hostname = $matches['host'];
	}

	/**
	 * @return string
	 */
	public function getHostname() {
		return $this->hostname;
	}

	/**
	 * @return string
	 */
	public function getNick() {
		return $this->nick;
	}

	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}
}
