<?php

namespace GoodIRC\IPCPacket;

abstract class BasePacket {

	/**
	 * Returns a new Packet
	 *
	 * @return BasePacket
	 */
	public static function create() {
		return new static;
	}
}
