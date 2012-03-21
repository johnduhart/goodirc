<?php

namespace GoodIRC;

use GoodIRC\IPCPacket\HelloPacket;
use GoodIRC\IPCPacket\ConfigurationPacket;

/**
 * Class that controls the IRC and Parsing processes
 */
class Master extends Context implements IContext {

	/**
	 * Master instance
	 *
	 * @var Master
	 */
	private static $instance = null;

	/**
	 * Gets the Master instance
	 *
	 * @return Master
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Logging instance
	 *
	 * @var \Monolog\Logger
	 */
	private $logger;

	/**
	 * Configuration file
	 *
	 * @var string
	 */
	private $configFile;

	/**
	 * Current configuration
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Socket listener
	 *
	 * @var IPCSocket
	 */
	private $listenSocket;

	/**
	 * IRC PHP Process
	 *
	 * @var Process
	 */
	public $ircProcess;

	/**
	 * IRC Socket
	 *
	 * @var IPCSocket
	 */
	private $ircSocket;

	/**
	 * Path of the IRC socket used for IRC<->system
	 *
	 * @var IPCPacket\IRCSocketPacket
	 */
	private $ircSocketPath;

	/**
	 * System PHP Process
	 *
	 * @var Process
	 */
	private $systemProcess;

	/**
	 * System socket
	 *
	 * @var IPCSocket
	 */
	private $systemSocket;

	/**
	 * Private constructor
	 */
	private function __construct() {
		$this->logger = GoodIRC::getLogger( 'master' );
	}

	/**
	 * Starts the processes
	 *
	 * @param $configFile
	 */
	public function run( $configFile ) {
		// Store the configuration path and load the config
		$this->configFile = realpath( $configFile ) ?: $configFile;
		$this->loadConfig();

		$this->logger->info( 'Starting master process' );

		// Create our listener
		list( $socket, $this->listenSocket ) = IPCSocket::createListener();

		// Creates the processes
		$this->ircProcess = new Process( "export XDEBUG_CONFIG=\"idekey=session_name\" && php irc.php $socket -dxdebug.remote_enable=1 -dxdebug.remote_host=127.0.0.1 -dxdebug.remote_port=9000 -dxdebug.remote_mode=req -dxdebug.remote_autostart=1", __DIR__ . '/../../bin/' );
		$this->systemProcess = new Process( "php system.php $socket", __DIR__ . '/../../bin/' );

		// Connect to the IRC process
		$this->connectIRCProcess();
		$this->logger->info( 'IRC process connected' );

		// Perform the handshake and setup for IRC
		$this->setupIRCProcess();

		// Connect the System process
		$this->connectSystemProcess();
		$this->logger->info( 'System process connected' );

		// Perform the system process handshake
		$this->setupSystemProcess();

		// Wait for the processes to be ready
		$this->waitForReady();

		// We're ready to go, change the state
		$this->changeState( Context::STATE_CONNECTING );

		// And now we loop
		while ( true ) {
			// TODO: Do important master stuffs
			usleep( 200 );
		}
	}

	/**
	 * Updates the state and sends a packet out to the processes
	 *
	 * @param $state
	 */
	protected function changeState( $state ) {
		parent::changeState( $state );
		$statePacket = IPCPacket\StateChangePacket::create()->setState( $state );
		$this->ircSocket->write( $statePacket );
		$this->systemSocket->write( $statePacket );
	}

	/**
	 * Loads the configuration
	 */
	private function loadConfig() {
		$contents = file_get_contents( $this->configFile );
		$this->config = eval( $contents );
	}

	/**
	 * Connects to a subprocess and handles any errors encountered
	 *
	 * @param Process $process
	 * @param $destination
	 * @param $type
	 * @param $name
	 */
	private function connectProcess( Process $process, &$destination, $type, $name ) {
		// Start the IRC process
		$process->start();
		$this->logger->info( "Starting $name process" );

		// Loop while we wait for the IRC process to start
		while ( true ) {
			// Make sure the process is still running
			if ( !$process->isRunning() ) {
				// Okay the process has died, wtf
				$exitCode = $process->getExitCode();
				$this->logger->info( "$name process has exited with an exit code of $exitCode" );
				switch ( $exitCode ) {
					case 0:
					case 1:
						// The Process ended normally, hmph. Restart it
						$process->start();
						$this->logger->info( "Restarting $name process" );
						break;

					case 255:
						// Syntax error
						$this->logger->info( "There was a PHP error when trying to start the $name process." );
						$this->logger->info( "Errors below:\n-----\n" );
						// PHP outputs errors to stdout. wat.
						$this->logger->info( $process->readStdout());
						exit;
						break;
				}
			}

			// Try to accept a new client
			if ( ( $client = $this->listenSocket->acceptClient() ) !== false ) {
				// We have a new client
				$this->logger->info( 'Client connected to master process' );

				// Give them 15 seconds to say hello
				$timeout = time() + 15;

				while ( $timeout > time() ) {
					if ( ( $packet = $client->read() ) === false ) {
						// usleep so we don't waste CPU
						usleep( 200 );
						continue;
					}

					// We got a packet! Is it a HelloPacket? Is it from the right process?
					if ( !( $packet instanceof HelloPacket ) || $packet->type != $type ) {
						// Fuck them.
						$this->logger->debug( 'Invalid packet recieved, disconnected' );
						$client->close();
						break;
					}

					// We now have a connection to the IRC process
					$destination = $client;
					break 2;
				}

				// We've timed out, bye!
				$this->logger->info( 'Client has timed out' );
				$client->close();
			}
		}
	}

	/**
	 * Starts and connects to the IRC process
	 */
	private function connectIRCProcess() {
		$this->connectProcess( $this->ircProcess, $this->ircSocket, HelloPacket::TYPE_IRC, 'IRC' );
	}

	/**
	 * Sets up the IRC process
	 */
	private function setupIRCProcess() {
		// Start by sending a Hello packet
		$this->ircSocket->write( self::getHelloPacket() );

		// Then the configuration
		$this->ircSocket->write( ConfigurationPacket::create()->setConfig( $this->config ) );

		// Wait for the IRC socket
		$this->ircSocketPath = GoodIRC::expectPacket( $this->ircSocket, new IPCPacket\IRCSocketPacket );
		$this->logger->info( 'Recieved IRC socket path' );
	}

	/**
	 * Sets up and connects to the system process
	 */
	private function connectSystemProcess() {
		$this->connectProcess( $this->systemProcess, $this->systemSocket, HelloPacket::TYPE_SYSTEM, 'System' );
	}

	/**
	 * Does the secret handshake with the system process
	 */
	private function setupSystemProcess() {
		// Say Hello
		$this->systemSocket->write( self::getHelloPacket() );

		// Then send the configuration
		$this->systemSocket->write( ConfigurationPacket::create()->setConfig( $this->config ) );

		// Now send our IRC socket packet we got from IRC
		$this->systemSocket->write( $this->ircSocketPath );
	}

	/**
	 * Wait for both processes to be ready
	 */
	private function waitForReady() {
		$this->logger->info( 'Waiting for the sub processes' );
		GoodIRC::expectPacket( $this->ircSocket, new IPCPacket\ProcessReadyPacket );
		GoodIRC::expectPacket( $this->systemSocket, new IPCPacket\ProcessReadyPacket );
		$this->logger->info( 'Processes ready!' );
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
	 * Returns a Hello packet for the master process
	 *
	 * @return HelloPacket
	 */
	private static function getHelloPacket() {
		return HelloPacket::create()->setType( HelloPacket::TYPE_MASTER );
	}
}