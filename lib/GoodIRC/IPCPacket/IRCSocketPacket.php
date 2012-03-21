<?php

namespace GoodIRC\IPCPacket;

class IRCSocketPacket extends BasePacket {
	/**
	 * Path of the socket to connect to IRC
	 *
	 * @var string
	 */
	public $socketPath;

	/**
	 * @param string $socketPath
	 * @return \GoodIRC\IPCPacket\IRCSocketPacket
	 */
	public function setSocketPath( $socketPath ) {
		$this->socketPath = $socketPath;
		return $this;
	}
}
