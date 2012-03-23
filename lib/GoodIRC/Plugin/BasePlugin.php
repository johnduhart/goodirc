<?php

namespace GoodIRC\Plugin;

use GoodIRC\System;

/**
 * Base plugin class
 */
abstract class BasePlugin {

	/**
	 * System that the plugin is for
	 *
	 * @var \GoodIRC\System
	 */
	protected $system;

	/**
	 * @param \GoodIRC\System $system
	 */
	public function __construct( System $system ) {
		$this->system = $system;
	}

	/**
	 * Any sort of setup
	 */
	public function setup() {}

	/**
	 * Notice message
	 *
	 * @param \GoodIRC\IRCMessage\NoticeMessage $notice
	 */
	public function onNotice( \GoodIRC\IRCMessage\NoticeMessage $notice ) {}

	/**
	 * User message
	 *
	 * @param \GoodIRC\IRCMessage\UserMessage $user
	 */
	public function onUser( \GoodIRC\IRCMessage\UserMessage $user ) {}

	/**
	 * Join message
	 *
	 * @param \GoodIRC\IRCMessage\JoinMessage $join
	 */
	public function onJoin( \GoodIRC\IRCMessage\JoinMessage $join ) {}

	/**
	 * PRIVMSG
	 *
	 * @param \GoodIRC\IRCMessage\PrivmsgMessage $privmsg
	 */
	public function onPrivmsg( \GoodIRC\IRCMessage\PrivmsgMessage $privmsg ) {}

	/**
	 * Ping
	 *
	 * @param \GoodIRC\IRCMessage\PingMessage $ping
	 */
	public function onPing( \GoodIRC\IRCMessage\PingMessage $ping ) {}

	/**
	 * Pong
	 *
	 * @param \GoodIRC\IRCMessage\PongMessage $pong
	 */
	public function onPong( \GoodIRC\IRCMessage\PongMessage $pong ) {}

	/**
	 * MOTD message line
	 *
	 * @param \GoodIRC\IRCMessage\MotdMessage $message
	 */
	public function onMotd( \GoodIRC\IRCMessage\MotdMessage $message ) {}

	/**
	 * MOTD start
	 *
	 * @param \GoodIRC\IRCMessage\MotdStartMessage $message
	 */
	public function onMotdStart( \GoodIRC\IRCMessage\MotdStartMessage $message ) {}

	/**
	 * MOTD end
	 *
	 * @param \GoodIRC\IRCMessage\MotdEndMessage $end
	 */
	public function onMotdEnd( \GoodIRC\IRCMessage\MotdEndMessage $end ) {}
}
