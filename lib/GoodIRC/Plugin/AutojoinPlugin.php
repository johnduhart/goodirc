<?php

namespace GoodIRC\Plugin;

use GoodIRC\System;
use GoodIRC\IRCMessage\JoinMessage;
use GoodIRC\IRCMessage\PrivmsgMessage;

class AutojoinPlugin extends BasePlugin {
	public function setup() {
		$this->system->registerCommand( 'hi', array( $this, 'commandHi' ) );
		$this->system->registerCommand( 'say', array( $this, 'commandSay' ), array( 's' ) );
		$this->system->registerCommand( 'join', array( $this, 'commandJoin' ) );
	}

	public function onMotdEnd( \GoodIRC\IRCMessage\MotdEndMessage $end ) {
		$this->system->sendIrcMessage( new JoinMessage( '#test' ) );
		$this->system->sendIrcMessage( new PrivmsgMessage( '#test', 'compwhizii: You suck' ) );
	}

	public function commandHi( PrivmsgMessage $msg ) {
		$this->system->sendIrcMessage( new PrivmsgMessage( $msg->getDestination(), $msg->getNick() . ': Hi!' ) );
	}

	public function commandJoin( PrivmsgMessage $msg, $channel ) {
		$this->system->sendIrcMessage( new JoinMessage( $channel ) );
	}

	public function commandSay( PrivmsgMessage $msg, $say ) {
		$this->system->sendIrcMessage( new PrivmsgMessage( $msg->getDestination(), $say ) );
	}

}
