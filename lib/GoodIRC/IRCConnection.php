<?php

namespace GoodIRC;

class IRCConnection {

	private static $ircMessages = array(
		'NOTICE' => 'NoticeMessage',
		'USER' => 'UserMessage',
		'JOIN' => 'JoinMessage',
		'PRIVMSG' => 'PrivmsgMessage',
		'PING' => 'PingMessage',
		'PONG' => 'PongMessage',

		// Numeric commands
		'372' => 'MotdMessage',
		'375' => 'MotdStartMessage',
		'376' => 'MotdEndMessage',
	);

	/**
	 * IRC process
	 *
	 * @var IRC
	 */
	private $irc;

	/**
	 * IRc connection
	 *
	 * @var resource
	 */
	private $stream;

	/**
	 * Constructor
	 *
	 * @param IRC $ircProcess
	 */
	public function __construct( IRC $ircProcess ) {
		$this->irc = $ircProcess;
	}

	/**
	 * Opens a connection to the server
	 */
	public function connect() {
		$ircConfig = $this->irc->getconfig();
		$ircConfig = $ircConfig['irc'];

		$socket = @stream_socket_client(
			"tcp://{$ircConfig['server']}:{$ircConfig['port']}", $errno, $errstr
		);

		if ( $socket === false ) {
			$this->irc->getLogger()->err( "IRC socket connection failed: $errno $errstr" );
			exit;
			// TODO: Graceful handling
		}

		stream_set_blocking( $socket, 0 );
		$this->stream = $socket;
	}

	/**
	 * Reads the current line into an IRC message object
	 * @return \GoodIRC\IRCMessage\NoticeMessage|null|string
	 */
	public function read() {
		$line = $this->rawRead();

		if ( $line === null ) {
			return $line;
		}

		// Blow that shit up (to get the command)
		list( $a, $b ) = explode( ' ', $line );

		// Sometimes the command comes second, sometimes it comes first
		if ( isset( self::$ircMessages[$b] ) ) {
			$command = $b;
		} elseif ( isset( self::$ircMessages[$a] ) ) {
			$command = $a;
		} else {
			GoodIRC::log()->warn( "Unkown IRC message: $line" );
			return null;
		}

		$class = "\\GoodIRC\\IRCMessage\\" . self::$ircMessages[$command];
		$message = new $class;
		$message->parse( $line );

		GoodIRC::log()->debug( "IRC Line ($command): $line" );
		GoodIRC::log()->debug( print_r($message,1));

		return $message;
	}

	public function write( IRCMessage\BaseMessage $message ) {
		GoodIRC::log()->debug( "Sending IRC message " . print_r( $message, 1 ) );
		fwrite( $this->stream, (string) $message . "\n" );
	}

	public function writeRaw( $str ) {
		fwrite( $this->stream, $str );
	}

	/**
	 * Reads a line directly from the buffer
	 *
	 * @return null|string
	 */
	protected function rawRead() {
		// Read into a buffer
		$buffer = '';
		do {
			$buffer .= fgets( $this->stream, 512 );
		} while ( !empty( $buffer ) && !preg_match( '/\v+$/', $buffer ) );
		$buffer = trim( $buffer );

		// Return null if the buffer is empty
		if ( empty( $buffer ) ) {
			return null;
		}

		return $buffer;
	}
}
