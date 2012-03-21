<?php

namespace GoodIRC\Plugin;

class MotdPlugin extends BasePlugin {
	public function onMotd( \GoodIRC\IRCMessage\MotdMessage $message ) {
		\GoodIRC\GoodIRC::log()->info( 'MOTD' );
	}

}
