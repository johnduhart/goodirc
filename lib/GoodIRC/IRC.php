<?php

namespace GoodIRC;

use GoodIRC\IPCPacket\HelloPacket;
use GoodIRC\IPCPacket\ConfigurationPacket;
use GoodIRC\IPCPacket\IRCMessagePacket;

class IRC extends Context implements IContext {
	/**
	 * IRC instance
	 *
	 * @var IRC
	 */
	private static $instance = null;

	/**
	 * Gets the IRc instance
	 *
	 * @return IRC
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * @var \Monolog\Logger
	 */
	private $logger;

	/**
	 * IRC Connection
	 *
	 * @var IRCConnection
	 */
	private $ircConnection;

	/**
	 * IPC connection to the master process
	 *
	 * @var IPCSocket
	 */
	private $masterSocket;

	/**
	 * Socket listening to clients
	 *
	 * @var IPCSocket
	 */
	private $listenSocket;

	/**
	 * Socket to the system
	 *
	 * @var IPCSocket
	 */
	private $systemSocket;

	/**
	 * Configuration
	 *
	 * @var array
	 */
	private $config;

	/**
	 * IRC lines waiting to be sent over
	 *
	 * @var array
	 */
	private $queuedIrcLines = array();

	/**
	 * Private constructor
	 */
	private function __construct() {
		$this->logger = GoodIRC::getLogger( 'irc' );
		$this->ircConnection = new IRCConnection( $this );
	}

	/**
	 * Run the IRC process
	 *
	 * @param $masterSocketPath
	 */
	public function run( $masterSocketPath ) {
		$this->logger->info( 'Starting IRC process' );

		// Connect to the master
		$this->masterSocket = IPCSocket::createConnection( $masterSocketPath );

		// Start by sending the Hello packet
		$this->masterSocket->write( self::getHelloPacket() );
		$this->logger->debug( 'Wrote hello packet' );

		// Wait for the master process to respond
		GoodIRC::expectPacket( $this->masterSocket, new HelloPacket, function (HelloPacket $p) {
			return $p->type == HelloPacket::TYPE_MASTER;
		} );

		// The next packet we'll get is the Configuration
		$packet = GoodIRC::expectPacket( $this->masterSocket, new ConfigurationPacket );
		$this->logger->info( 'Configuration recieved' );
		$this->config = $packet->config;

		// Create the listening socket
		list( $socketPath, $this->listenSocket ) = IPCSocket::createListener();
		$this->logger->debug( "Created socket for IRC: $socketPath" );

		// Inform the master process of the socket
		$this->masterSocket->write( IPCPacket\IRCSocketPacket::create()->setSocketPath( $socketPath ) );

		// That's it for the setup process, now we wait for the System process
		// to connect and the master process to signal a start
		$this->logger->debug( 'IRC Setup done' );

		// Wait for that system process
		while ( true ) {
			if ( ( $client = $this->listenSocket->acceptClient() ) === false ) {
				usleep( 200 );
				continue;
			}

			$this->logger->debug( 'Client connected to IRC process' );

			// Wait for them to say hello
			$timeout = time() + 15;

			while ( $timeout > time() ) {
				if ( ( $packet = $client->read() ) === false ) {
					// usleep so we don't waste CPU
					usleep( 200 );
					continue;
				}

				// We got a packet! Is it a HelloPacket? Is it from the right process?
				if ( !( $packet instanceof HelloPacket ) || $packet->type != HelloPacket::TYPE_SYSTEM ) {
					// Fuck them.
					$this->logger->debug( 'Invalid packet recieved, disconnected' );
					$client->close();
					break;
				}

				$this->logger->info( 'System process connected' );
				$this->systemSocket = $client;
				break 2;
			}

			// We've timed out, bye!
			$this->logger->info( 'Client has timed out' );
			$client->close();
		}

		// We're connected, say hi and we're ready to go
		$this->systemSocket->write( self::getHelloPacket() );
		$this->masterSocket->write( new IPCPacket\ProcessReadyPacket );

		// Wait for the state shift
		GoodIRC::expectPacket( $this->masterSocket, new IPCPacket\StateChangePacket, function ( IPCPacket\StateChangePacket $p ) {
			return $p->state == Context::STATE_CONNECTING;
		});
		$this->state = Context::STATE_CONNECTING;

		// Now for the moment we've all been waiting for, connecting to the IRC server
		$this->ircConnection->connect();

		// Auth
		$this->ircConnection->write( new IRCMessage\NickMessage( $this->config['irc']['nick'] ) );
		$this->ircConnection->write( new IRCMessage\UserMessage(
			$this->config['irc']['user'], $this->config['irc']['realname'] ) );
		//$this->ircConnection->writeRaw('CAPEDFWefdwaef LIST');

		while ( true ) {
			// Read new IRC lines
			$this->readIRCLines();

			// Send said lines out to the system
			$this->relayIRCLines();

			// Get IRC lines from the system process and write them
			$this->transmitIRCLines();

			// TODO: proper tick system
			usleep( 300 );
		}
	}

	/**
	 * Reads IRC lines from the irc socket
	 */
	private function readIRCLines() {
		// Read a line
		do {
			$line = $this->ircConnection->read();

			if ( $line !== null ) {
				$this->queuedIrcLines[] = $line;
			}
		} while( $line !== null );
	}

	/**
	 * Sends IRC lines to the system process
	 *
	 * @return mixed
	 */
	private function relayIRCLines() {
		if ( !count( $this->queuedIrcLines ) ) {
			return;
		}

		$lines = $this->queuedIrcLines;

		/** @var IRCMessage\BaseMessage $line */
		foreach( $lines as $line ) {
			$this->systemSocket->write( IRCMessagePacket::create()->setMessage( $line ) );

			// Hanle pings internally, this means if the connection
			// with system is lost we stay connected
			if ( $line instanceof \GoodIRC\IRCMessage\PingMessage ) {
				$this->ircConnection->write( $line->getPong() );
			}
		}

		$this->queuedIrcLines = array();
	}

	/**
	 * Writes a message IRC
	 *
	 * @return mixed
	 */
	private function transmitIRCLines() {
		do {
			$message = $this->systemSocket->read();

			if ( $message === false ) {
				return;
			}

			$this->ircConnection->write( $message->message );
		} while( $message !== false );
	}

	/**
	 * Returns a hello packet for the IRC process
	 *
	 * @return IPCSocket\HelloPacket
	 */
	private static function getHelloPacket() {
		return HelloPacket::create()->setType( HelloPacket::TYPE_IRC );
	}

	/**
	 * Returns a monolog instance
	 *
	 * @return \Monolog\Logger
	 */
	public function getLogger() {
		return $this->logger;
	}

	/**
	 * @return array
	 */
	public function getConfig() {
		return $this->config;
	}
}
