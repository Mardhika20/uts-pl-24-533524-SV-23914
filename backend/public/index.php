<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;


define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$source = '';
$line = 0;

$alreadySent = headers_sent($source, $line);

error_log(
    '[P10-BEFORE] sent=' . ($alreadySent ? 'yes' : 'no')
    . ' source=' . $source . ':' . $line
);

$app->handleRequest(Request::capture());

$source = '';
$line = 0;

$sent = headers_sent($source, $line);

error_log(
    '[P10-DEBUG] headers_sent=' . ($sent ? 'yes' : 'no')
    . ' first_output=' . $source . ':' . $line
    . ' status=' . http_response_code()
);


