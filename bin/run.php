<?php

require_once __DIR__ . '/../autoload.php';

use GoodIRC\GoodIRC;
use GoodIRC\Master;

// Require a configuration file
if ( $argc < 2 ) {
	echo "A configuration file is required\n";
	exit;
}

register_shutdown_function( function () {
	echo Master::getInstance()->ircProcess->getExitCode();
} );

// Start the daemon process
GoodIRC::setContext( Master::getInstance() )->run( $argv[1] );