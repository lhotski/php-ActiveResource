<?php

require_once 'PHPUnit/Framework.php';
require_once __DIR__ . '/../UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespace('ActiveResource', realpath(__DIR__ . '/../src'));
$loader->register();
