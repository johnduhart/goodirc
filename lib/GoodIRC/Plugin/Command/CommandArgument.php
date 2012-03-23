<?php

namespace GoodIRC\Plugin\Command;

class CommandArgument {
	/**
	 * Name of the parameter
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * If it's required
	 *
	 * @var bool
	 */
	public $required = true;

	public function __construct( $name, $required ) {
		$this->name = $name;
		$this->required = $required;
	}
}
