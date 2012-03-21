<?php

namespace GoodIRC\IPCPacket;

use GoodIRC\IRCMessage\BaseMessage;

class IRCMessagePacket extends BasePacket {
	/**
	 * Message to send
	 *
	 * @var BaseMessage
	 */
	public $message;

	/**
	 * @param \GoodIRC\IRCMessage\BaseMessage $message
	 * @return \GoodIRC\IPCPacket\IRCMessagePacket
	 */
	public function setMessage( BaseMessage $message ) {
		$this->message = $message;
		return $this;
	}
}
