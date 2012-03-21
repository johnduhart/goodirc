<?php

namespace GoodIRC\IPCPacket;

/**
 * First packet sent as part of the IPC handshake
 */
class HelloPacket extends BasePacket {
	/**
	 * Hello packet from the master process
	 */
	const TYPE_MASTER = 1;

	/**
	 * Hello packet from the IRC process
	 */
	const TYPE_IRC = 2;

	/**
	 * Hello packet from the system process
	 */
	const TYPE_SYSTEM = 3;

	/**
	 * Procss type this IPC packet is originating from
	 *
	 * @var int
	 */
	public $type;

	/**
	 * @param int $type
	 * @return \GoodIRC\IPCPacket\HelloPacket
	 */
	public function setType( $type ) {
		$this->type = $type;
		return $this;
	}
}
