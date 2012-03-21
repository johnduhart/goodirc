<?php

namespace GoodIRC;

class Process {

	/**
	 * Descriptor spec for processes
	 *
	 * @var array
	 */
	private static  $discriptorSpec = array(
		0 => array( 'pipe', 'r' ),
		1 => array( 'pipe', 'w' ),
		2 => array( 'pipe', 'w' ),
	);

	/**
	 * Comamnd name
	 *
	 * @var string
	 */
	private $command;

	/**
	 * Working directory
	 *
	 * @var null|string
	 */
	private $cwd = null;

	/**
	 * @var null|string
	 */
	private $env = null;

	/**
	 * Process resource
	 *
	 * @var resource
	 */
	private $process;

	/**
	 * Process pipes
	 *
	 * @var array
	 */
	private $pipes;

	/**
	 * Exit code of the process
	 *
	 * @var int
	 */
	private $exitCode;

	/**
	 * Process constructor
	 *
	 * @param $command
	 * @param null $cwd
	 */
	public function __construct( $command, $cwd = null, $env = null ) {
		$this->command = $command;
		$this->cwd = $cwd;
		$this->env = $env;
	}

	/**
	 * Starts the process
	 */
	public function start() {
		$this->process = proc_open( $this->command, self::$discriptorSpec, $this->pipes, $this->cwd, $this->env );
	}

	/**
	 * Checks to see if the process is still running
	 *
	 * @return bool
	 */
	public function isRunning() {
		$status = $this->getStatus();
		return $status['running'];
	}

	/**
	 * Returns the exit code of the process
	 *
	 * @return null|int
	 */
	public function getExitCode() {
		if ( $this->isRunning() ) {
			return null;
		}

		return $this->exitCode;
	}

	/**
	 * Returns the stdout buffer
	 *
	 * @return string
	 */
	public function readStdout() {
		$output = '';
		while ( $line = fread( $this->pipes[1], 1024 ) ) {
			$output .= $line;
		}

		return $output;
	}

	/**
	 * Returns the status of the process
	 *
	 * @return array
	 */
	protected function getStatus() {
		$status = proc_get_status( $this->process );

		if ( $status['exitcode'] !== -1 ) {
			$this->exitCode = $status['exitcode'];
		}

		return $status;
	}
}
