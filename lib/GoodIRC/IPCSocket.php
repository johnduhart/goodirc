<?php

namespace GoodIRC;

use GoodIRC\IPCPacket\BasePacket;

class IPCSocket {

	/**
	 * Default mode
	 */
	const MODE_NONE = 0;

	/**
	 * Listening mode
	 */
	const MODE_LISTEN = 1;

	/**
	 * Connected mode
	 */
	const MODE_CONNECT = 2;

	/**
	 * Client mode
	 */
	const MODE_CLIENT = 3;

	/**
	 * Socket resource
	 *
	 * @var resource
	 */
	private $socket;

	/**
	 * Mode
	 *
	 * @var int
	 */
	private $mode = IPCSocket::MODE_NONE;

	/**
	 * Data left over after reading the socket
	 *
	 * @var string
	 */
	private $extraBytes;

	/**
	 * Creates a listener socket
	 *
	 * @return array
	 */
	public static function createListener() {
		$socketPath = '/tmp/goodirc-' . md5( posix_getpid() . mt_rand() ) . '.sock';
		$socket = new IPCSocket();
		$socket->listen( $socketPath );

		return array( $socketPath, $socket );
	}

	/**
	 * Creates a new connection to the master
	 *
	 * @param $socketPath
	 * @return \GoodIRC\IPCSocket
	 */
	public static function createConnection( $socketPath ) {
		$socket = new IPCSocket();
		$socket->connect( $socketPath );

		return $socket;
	}

	/**
	 * Creates a client socket
	 *
	 * @param $clientSocket
	 * @return \GoodIRC\IPCSocket
	 */
	public static function createClient( $clientSocket ) {
		socket_set_nonblock( $clientSocket );
		$socket = new IPCSocket();
		$socket->socket = $clientSocket;
		$socket->mode = IPCSocket::MODE_CLIENT;

		return $socket;
	}

	/**
	 * Creates a new socket instance
	 */
	public function __construct() {
		$this->socket = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		socket_set_nonblock( $this->socket );
	}

	/**
	 * Listens on the socket
	 *
	 * @param string $socketPath Path to the socket
	 */
	public function listen( $socketPath ) {
		socket_bind( $this->socket, $socketPath );
		socket_listen( $this->socket );

		$this->mode = IPCSocket::MODE_LISTEN;
	}

	/**
	 * Connects to the socket
	 *
	 * @param string $socketPath Path to the socket
	 */
	public function connect( $socketPath ) {
		socket_connect( $this->socket, $socketPath );

		$this->mode = IPCSocket::MODE_CONNECT;
	}

	/**
	 * Closes the socket
	 */
	public function close() {
		socket_close( $this->socket );
	}

	/**
	 * Accepts a client
	 *
	 * @return IPCSocket|bool
	 */
	public function acceptClient() {
		if ( $this->mode != IPCSocket::MODE_LISTEN ) {
			throw new \Exception( 'Tried to call acceptClient while not in listening mode' );
		}

		// socket_accept throws errors when there isn't a new client. Thanks PHP
		$socket = @socket_accept( $this->socket );

		if ( $socket !== false ) {
			return IPCSocket::createClient( $socket );
		}

		return $socket;
	}

	/**
	 * Reads a packet from the socket
	 *
	 * @return bool|mixed
	 */
	public function read() {
		$data = '';

		while ( true ) {
			if ( $this->extraBytes == '' ) {
				// If there's nothing in the left over, load the buffer freah
				$buffer = socket_read( $this->socket, 1024 );
			} else {
				// Otherwise take the buffer from the left over
				$buffer = $this->extraBytes;
				$this->extraBytes = '';
			}

			// Nothing read
			if ( $buffer === '' || $buffer === false ) {
				return false;
			}

			// Look for the NULL chracter
			$endPos = strpos( $buffer, "\0" );

			if ( $endPos === false ) {
				// No NULL character, add the buffer to the return data
				$data .= $buffer;
			} else {
				// Found it, add the data before the NULL to the data
				$data .= substr( $buffer, 0, $endPos );
				// Add one to skip over the NULL character
				$endPos += 1;
				// If there's still stuff, add it to the left over
				if ( $endPos < strlen( $buffer ) ) {
					$this->extraBytes = substr( $buffer, $endPos );
				}
				break;
			}
		}

		return unserialize( str_replace( "\7", "\0", $data ) );
	}

	/**
	 * Wait to recieve a packet
	 *
	 * @param $timeout
	 * @return BasePacket|null
	 */
	public function waitForPacket( $timeout ) {
		$timeout = $timeout + time();

		while ( $timeout > time() ) {
			if ( ( $packet = $this->read() ) === false ) {
				// usleep so we don't waste CPU
				usleep( 200 );
				continue;
			}

			// We got a packet! Return it!
			return $packet;
		}

		// Timeout
		return null;
	}

	/**
	 * Waits for a packet or exits
	 *
	 * @param $timeout
	 * @param int $exitCode
	 * @return IPCPacket\BasePacket|null
	 */
	public function waitForPacketOrDie( $timeout, $exitCode = 1 ) {
		$packet = $this->waitForPacket( $timeout );

		if ( $packet !== null ) {
			return $packet;
		}

		GoodIRC::log()->err( 'Timed out waiting for packet' );
		exit( $exitCode );
	}

	/**
	 * Writes a packet to the socket
	 *
	 * @param IPCPacket\BasePacket $packet
	 */
	public function write( BasePacket $packet ) {
		// Replace null character with the bell char
		$str = str_replace( "\0", "\7", serialize( $packet ) ) . "\0";

		socket_write( $this->socket, $str );
	}
}
