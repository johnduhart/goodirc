<?php

namespace GoodIRC\Plugin;

use GoodIRC\System;
use GoodIRC\IRCMessage\JoinMessage;
use GoodIRC\IRCMessage\PrivmsgMessage;

class AutojoinPlugin extends BasePlugin {
	public function onMotdEnd( \GoodIRC\IRCMessage\MotdEndMessage $end, System $system ) {
		$system->sendIrcMessage( new JoinMessage( '#test' ) );
		$system->sendIrcMessage( new PrivmsgMessage( '#test', 'compwhizii: You suck' ) );
	}

}
