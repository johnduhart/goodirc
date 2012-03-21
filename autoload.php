<?php

require_once __DIR__ . '/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
	'GoodIRC' => __DIR__ . '/lib',
	'Monolog' => __DIR__ . '/vendor/monolog/src'
));
$loader->register();

ini_set( 'display_errors', '1' );
error_reporting( E_ALL );