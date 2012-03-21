<?php

namespace GoodIRC;

use GoodIRC\IRCMessage\BaseMessage;
use GoodIRC\IPCPacket\HelloPacket;
use GoodIRC\IPCPacket\IRCMessagePacket;
use GoodIRC\Plugin\BasePlugin;

class System extends Context implements IContext {

	/**
	 * System instance
	 *
	 * @var System
	 */
	private static $instance = null;

	/**
	 * Gets the System instance
	 *
	 * @return System
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Logger instance
	 *
	 * @var \Monolog\Logger
	 */
	private $logger;

	/**
	 * IPC connection to the master process
	 *
	 * @var IPCSocket
	 */
	private $masterSocket;

	/**
	 * @var IPCSocket
	 */
	private $ircSocket;

	/**
	 * Configuration
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Plugins
	 *
	 * @var array
	 */
	private $plugins = array();

	/**
	 * Private constructor
	 */
	private function __construct() {
		$this->logger = GoodIRC::getLogger( 'system' );
	}

	/**
	 * Run the system
	 *
	 * @param $masterSocketPath
	 */
	public function run( $masterSocketPath ) {
		$this->logger->info( 'Starting System process' );

		// Connect to the master
		$this->masterSocket = IPCSocket::createConnection( $masterSocketPath );

		// Start by sending the Hello packet
		$this->masterSocket->write( self::getHelloPacket() );

		// Get a hello back
		GoodIRC::expectPacket( $this->masterSocket, new HelloPacket, function ( HelloPacket $p ) {
			return $p->type == HelloPacket::TYPE_MASTER;
		} );

		// Get the configuration
		$packet = GoodIRC::expectPacket( $this->masterSocket, new IPCPacket\ConfigurationPacket );
		$this->config = $packet->config;
		$this->logger->debug( 'Recieved configuration' );

		// Load plugins
		$this->registerPlugin( 'join', new Plugin\AutojoinPlugin );

		// Get the path of the IRC socket
		$packet = GoodIRC::expectPacket( $this->masterSocket, new IPCPacket\IRCSocketPacket );
		$ircSocketPath = $packet->socketPath;

		// Connect to the IRC socket
		$this->logger->debug( 'Connecting to IRC socket' );
		$this->ircSocket = IPCSocket::createConnection( $ircSocketPath );

		// Say hi
		$this->ircSocket->write( self::getHelloPacket() );
		GoodIRC::expectPacket( $this->ircSocket, new HelloPacket, function ( HelloPacket $p ) {
			return $p->type == HelloPacket::TYPE_IRC;
		} );

		// Annnnd we're ready to go!
		$this->masterSocket->write( new IPCPacket\ProcessReadyPacket );

		// Wait for the state shift
		GoodIRC::expectPacket( $this->masterSocket, new IPCPacket\StateChangePacket, function ( IPCPacket\StateChangePacket $p ) {
			return $p->state == Context::STATE_CONNECTING;
		});
		$this->state = Context::STATE_CONNECTING;

		// Now for the main event (loop)
		while ( true ) {
			$this->processIRCMessage();

			// do shit
			usleep( 200 );
		}

	}

	private function processIRCMessage() {
		$packet = $this->ircSocket->read();

		// No new packet
		if ( $packet === false ) {
			return;
		}

		// IRC message packet, send it off for multiation
		if ( $packet instanceof \GoodIRC\IPCPacket\IRCMessagePacket ) {
			return $this->processIRCLine( $packet->message );
		}

		// This means that we got a packet we don't support. w/e
	}

	/**
	 * @param IRCMessage\BaseMessage $line
	 */
	private function processIRCLine( \GoodIRC\IRCMessage\BaseMessage $line ) {
		$this->logger->debug( 'Got IRC line: '. print_r( $line, 1 ) );

		// Convert the class name to a function name
		$className = explode( '\\', get_class( $line ) );
		$funcName = 'on' . substr( array_pop( $className ), 0, -7 );

		// Call the plugin
		foreach ( $this->plugins as $name => $plugin ) {
			call_user_func_array( array( $plugin, $funcName ), array( $line, $this ) );
		}
	}

	/**
	 * Registers a plugin
	 *
	 * @param $name
	 * @param Plugin\BasePlugin $plugin
	 */
	private function registerPlugin( $name, BasePlugin $plugin ) {
		$this->plugins[$name] = $plugin;
	}

	/**
	 * Sends an IRC message to the IRC process
	 *
	 * @param IRCMessage\BaseMessage $message
	 */
	public function sendIrcMessage( BaseMessage $message ) {
		$this->logger->debug( "Writing message!" );
		$this->ircSocket->write( IRCMessagePacket::create()->setMessage( $message ) );
	}

	/**
	 * Returns a HelloPacket for the system process
	 *
	 * @return IPCSocket\HelloPacket
	 */
	private static function getHelloPacket() {
		return HelloPacket::create()->setType( HelloPacket::TYPE_SYSTEM );
	}

	/**
	 * Returns a monolog instance
	 *
	 * @return \Monolog\Logger
	 */
	public function getLogger() {
		return $this->logger;
	}
}
