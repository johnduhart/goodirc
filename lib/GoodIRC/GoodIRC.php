<?php

namespace GoodIRC;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * Static class of utility functions
 */
final class GoodIRC {

	/**
	 * Current context
	 *
	 * @var IContext
	 */
	private static $context = null;

	/**
	 * Private constructor so the class can't be instanted
	 */
	private function __construct() {}

	/**
	 * Returns a Monoglog instance
	 *
	 * @param $channel
	 * @return \Monolog\Logger
	 */
	public static function getLogger( $channel ) {
		$logger = new Logger( $channel );
		$logger->pushHandler( new StreamHandler( realpath( __DIR__ . '/../../irc.log' ) ) );

		// Add error handlers
		set_error_handler(function( $errno, $errstr, $errfile, $errline ) use ($logger) {
			// This keeps silenced errors from being reported
			if ( error_reporting() === 0 ) {
				return;
			}

			$logger->addError( "PHP Error [$errno]: $errstr [$errfile line $errline]" );
		});

		set_exception_handler(function( $exception ) use ( $logger ) {
			$logger->addError( "PHP Exception: {$exception->getMessage()} [{$exception->getFile()} line {$exception->getLine()}]" );
		});

		return $logger;
	}

	/**
	 * Expects a packet of a certain type otherwise die
	 *
	 * @param IPCSocket $socket
	 * @param $packetType
	 * @param Closure|null $closure Closure for addtional checks
	 * @return IPCPacket\BasePacket|null
	 */
	public static function expectPacket( IPCSocket $socket, $packetType, \Closure $closure = null ) {
		$packet = $socket->waitForPacketOrDie( 10, 50 );
		if ( !( $packet instanceof $packetType) || ( $closure !== null && !$closure($packet) ) ) {
			// Invalid response
			GoodIRC::log()->err( 'Invalid packet, exiting' );
			exit(51);
		}

		return $packet;
	}

	/**
	 * Sets the current context
	 *
	 * @param IContext $context
	 * @return IContext
	 */
	public static function setContext( IContext $context ) {
		self::$context = $context;

		return $context;
	}

	/**
	 * Returns the current context
	 *
	 * @return IContext
	 * @throws \Exception
	 */
	public static function getContext() {
		if ( self::$context === null ) {
			throw new \Exception( __METHOD__ . ' was called without a set context' );
		}

		return self::$context;
	}

	/**
	 * Returns a logger instance
	 *
	 * @return \Monolog\Logger
	 */
	public static function log() {
		return self::getContext()->getLogger();
	}

	/**
	 * Creates a reflection for a callback
	 *
	 * @param $callback
	 * @return \ReflectionFunction|\ReflectionMethod
	 */
	public static function functionReflectionFactory( $callback ) {
		if (is_array($callback)) {
			// must be a class method
			list($class, $method) = $callback;
			return new \ReflectionMethod($class, $method);
		}

		// class::method syntax
		if (is_string($callback) && strpos($callback, "::") !== false) {
			list($class, $method) = explode("::", $callback);
			return new \ReflectionMethod($class, $method);
		}

		// objects as functions (PHP 5.3+)
		if (version_compare(PHP_VERSION, "5.3.0", ">=") && method_exists($callback, "__invoke")) {
			return new \ReflectionMethod($callback, "__invoke");
		}

		// assume it's a function
		return new \ReflectionFunction($callback);
	}
}
