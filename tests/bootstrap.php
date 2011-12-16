<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once __DIR__ . '/../UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespace('ActiveResource', realpath(__DIR__ . '/../src'));
$loader->register();

date_default_timezone_set('Europe/Minsk');
