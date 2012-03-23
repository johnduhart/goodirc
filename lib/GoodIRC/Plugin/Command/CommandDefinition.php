<?php

namespace GoodIRC\Plugin\Command;

use GoodIRC\GoodIRC;

class CommandDefinition {
	/**
	 * Callback when the command is run
	 *
	 * @var mixed
	 */
	public $callback;

	/**
	 * Arguments for the command
	 *
	 * @var array
	 */
	public $arguments = array();

	/**
	 * Creates a command definition from a callback
	 *
	 * @param $command
	 * @param $callback
	 * @return CommandDefinition
	 */
	public static function create( $command, $callback ) {
		$cmd = new self;
		$cmd->callback = $callback;

		$reflection = GoodIRC::functionReflectionFactory( $callback );

		$skip = true;
		/** @var $param \ReflectionParameter */
		foreach ( $reflection->getParameters() as $param ) {
			// Skip the first parameter, we pass the PRIVMSG to the command
			if ( $skip ) {
				$skip = false;
				continue;
			}

			$cmd->arguments[$param->getName()] = new CommandArgument(
				$param->getName(), !$param->isOptional()
			);
		}

		return $cmd;
	}

	/**
	 * If arguments are required
	 *
	 * @return int
	 */
	public function requireArguments() {
		/** @var $arg CommandArgument */
		$required = 0;
		foreach ( $this->arguments as $name => $arg ) {
			if ( $arg->required ) {
				$required++;
			}
		}

		return $required;
	}

}
