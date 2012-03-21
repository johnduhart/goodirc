<?php

namespace GoodIRC\IPCPacket;

/**
 * Packet for transmitting the configuration
 */
class ConfigurationPacket extends BasePacket {
	/**
	 * Configuration
	 *
	 * @var array
	 */
	public $config;

	/**
	 * @param $config
	 * @return ConfigurationPacket
	 */
	public function setConfig( $config ) {
		$this->config = $config;
		return $this;
	}
}
