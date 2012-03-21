<?php

namespace GoodIRC\IPCPacket;

class StateChangePacket extends BasePacket {
	/**
	 * New state that we are changing to
	 *
	 * @var int
	 */
	public $state;

	/**
	 * @param $state
	 * @return StateChangePacket
	 */
	public function setState( $state ) {
		$this->state = $state;
		return $this;
	}
}
