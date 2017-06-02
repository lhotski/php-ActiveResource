<?php

require 'vendor/autoload.php';

class Page extends ActiveResource\Base{}

$connection = new \ActiveResource\Connections\GuzzleConnection('http://localhost/');
$response_data = Page::find('all', $connection);
var_dump($response_data);
