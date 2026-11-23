<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// A fresh upload has no .env yet: start one from the example with a new key, marked as not
// installed so every page leads to the installer at /install. A server configured through real
// environment variables (Docker, Forge...) has no .env either, but it is set up already: leave it.
if (!file_exists($env = __DIR__.'/../.env') && file_exists(__DIR__.'/../.env.example')
    && getenv('APP_KEY') === false && getenv('DB_HOST') === false) {
    $key = 'APP_KEY=base64:'.base64_encode(random_bytes(32));
    $contents = preg_replace('/^APP_KEY=.*$/m', $key, file_get_contents(__DIR__.'/../.env.example'))."\nAPP_INSTALLED=false\n";

    if (@file_put_contents($env, $contents) === false) {
        http_response_code(500);
        exit('Clientverse cannot create its .env file. Make the application folder writable by the web server, then reload.');
    }
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
