<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Gapi.php';

use Google\Client;
use Google\Service\Drive;

$googleApi = new GoogleApi();
$googleApi->getClient();