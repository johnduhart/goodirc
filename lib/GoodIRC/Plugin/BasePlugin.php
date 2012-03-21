<?php

namespace GoodIRC\Plugin;

use GoodIRC\System;

/**
 * Base plugin class
 */
abstract class BasePlugin {
	/**
	 * Notice message
	 *
	 * @param \GoodIRC\IRCMessage\NoticeMessage $notice
	 * @param \GoodIRC\System $system
	 */
	public function onNotice( \GoodIRC\IRCMessage\NoticeMessage $notice, System $system ) {}

	/**
	 * User message
	 *
	 * @param \GoodIRC\IRCMessage\UserMessage $user
	 * @param \GoodIRC\System $system
	 */
	public function onUser( \GoodIRC\IRCMessage\UserMessage $user, System $system ) {}

	/**
	 * Join message
	 *
	 * @param \GoodIRC\IRCMessage\JoinMessage $join
	 * @param \GoodIRC\System $system
	 */
	public function onJoin( \GoodIRC\IRCMessage\JoinMessage $join, System $system ) {}

	/**
	 * PRIVMSG
	 *
	 * @param \GoodIRC\IRCMessage\PrivmsgMessage $privmsg
	 * @param \GoodIRC\System $system
	 */
	public function onPrivmsg( \GoodIRC\IRCMessage\PrivmsgMessage $privmsg, System $system ) {}

	/**
	 * Ping
	 *
	 * @param \GoodIRC\IRCMessage\PingMessage $ping
	 * @param \GoodIRC\System $system
	 */
	public function onPing( \GoodIRC\IRCMessage\PingMessage $ping, System $system ) {}

	/**
	 * Pong
	 *
	 * @param \GoodIRC\IRCMessage\PongMessage $pong
	 * @param \GoodIRC\System $system
	 */
	public function onPong( \GoodIRC\IRCMessage\PongMessage $pong, System $system ) {}

	/**
	 * MOTD message line
	 *
	 * @param \GoodIRC\IRCMessage\MotdMessage $message
	 * @param \GoodIRC\System $system
	 */
	public function onMotd( \GoodIRC\IRCMessage\MotdMessage $message, System $system ) {}

	/**
	 * MOTD start
	 *
	 * @param \GoodIRC\IRCMessage\MotdStartMessage $message
	 * @param \GoodIRC\System $system
	 */
	public function onMotdStart( \GoodIRC\IRCMessage\MotdStartMessage $message, System $system ) {}

	/**
	 * MOTD end
	 *
	 * @param \GoodIRC\IRCMessage\MotdEndMessage $end
	 * @param \GoodIRC\System $system
	 */
	public function onMotdEnd( \GoodIRC\IRCMessage\MotdEndMessage $end, System $system ) {}
}
