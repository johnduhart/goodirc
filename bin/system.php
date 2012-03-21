<?php

require_once __DIR__ . '/../autoload.php';

use GoodIRC\GoodIRC;
use GoodIRC\System;

GoodIRC::setContext( System::getInstance() )->run( $argv[1] );