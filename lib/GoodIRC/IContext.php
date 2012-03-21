<?php

namespace GoodIRC;

/**
 * Interface implemented by all contexts
 */
interface IContext {
	/**
	 * Runs the given context
	 *
	 * @param string $argument Optional argument
	 */
	public function run( $argument );

	/**
	 * Returns a monolog instance
	 *
	 * @return \Monolog\Logger
	 */
	public function getLogger();
}
