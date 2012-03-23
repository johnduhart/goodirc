<?php

namespace GoodIRC\IRCMessage;

use GoodIRC\IRCHostname;

abstract class BaseHostMessage extends BaseMessage {
	/**
	 * Hostname
	 *
	 * @var null|\GoodIRC\IRCHostname
	 */
	protected $host;

	/**
	 * Sets the host
	 *
	 * @param $host
	 */
	protected function setHost( $host ) {
		$this->host = new IRCHostname( $host );
	}

	/**
	 * @return \GoodIRC\IRCHostname|null
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getNick() {
		return $this->host->getNick();
	}

	/**
	 * @return string
	 */
	public function getHostname() {
		$this->host->getHostname();
	}

	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->host->getUsername();
	}
}
