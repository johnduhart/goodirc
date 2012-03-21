<?php

require_once __DIR__ . '/../autoload.php';

use GoodIRC\GoodIRC;
use GoodIRC\IRC;

GoodIRC::setContext( IRC::getInstance() )->run( $argv[1] );