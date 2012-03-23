<?php

namespace GoodIRC\Plugin;

use GoodIRC\Plugin\Command\CommandDefinition;

class CommandPlugin extends BasePlugin {
	/**
	 * @var array
	 */
	private $commands = array();

	/**
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Registers a command
	 *
	 * @param $name
	 * @param $callback
	 * @param array $aliases
	 */
	public function registerCommand( $name, $callback, $aliases = array() ) {
		$cmd = CommandDefinition::create( $name, $callback );

		$this->commands[$name] = $cmd;

		foreach ( $aliases as $alias ) {
			$this->aliases[$alias] = $name;
		}
	}

	public function onPrivmsg( \GoodIRC\IRCMessage\PrivmsgMessage $privmsg ) {
		list( $command, $args ) = explode( ' ', $privmsg->getMessage(), 2 );

		// Commands start with !
		if ( substr( $command, 0, 1 ) != '!' ) {
			return;
		}

		// Strip the ! off
		$command = substr( $command, 1 );

		/** @var $cmd Command\CommandDefinition */
		if ( isset( $this->commands[$command] ) ) {
			$cmd = $this->commands[$command];
		} elseif ( isset( $this->aliases[$command] ) ) {
			$cmd = $this->commands[$this->aliases[$command]];
		} else {
			return;
		}

		// The following is "borrowed" from phergie

		// If no arguments are passed...
		if ( empty( $args ) ) {

			// If the method requires no arguments, call it
			if ( !$cmd->requireArguments() ) {
				call_user_func( $cmd->callback, $privmsg );
			}

		} else {
			// If arguments are passed...

			// Parse the arguments
			if ( '"' == substr( $args, 0, 1 ) ) {
				preg_match_all( '/("[^"]*")|(\S+)/', $args, $args );
				$argsIn = $args[0];
				$i = 1;
				$args = array();
				$methodArgsTotal = count( $cmd->arguments );
				foreach ( $argsIn as $arg ) {
					if ( $i < $methodArgsTotal ) {
						$args[] = $arg;
						$i++;
					} else {
						if ( empty( $args[$methodArgsTotal] ) ) {
							$args[$methodArgsTotal] = $arg;
						} else {
							$args[$methodArgsTotal] .= ' ' . $arg;
						}
					}
				}
				$args = array_values( $args );
			} else {
				$args = preg_split( '/\s+/', $args, count( $cmd->arguments ) );
			}

			array_unshift( $args, $privmsg );

			// If the minimum arguments are passed, call the method
			if ( $cmd->requireArguments() <= count( $args ) ) {
				call_user_func_array(
					$cmd->callback,
					$args
				);
			}
		}
	}
}
