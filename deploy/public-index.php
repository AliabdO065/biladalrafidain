<?php
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));

// Shared-hosting layout: /home/USER/bilad is private, public_html contains only public assets.
$applicationRoot = dirname(__DIR__).'/bilad';
if (file_exists($maintenance = $applicationRoot.'/storage/framework/maintenance.php')) require $maintenance;
require $applicationRoot.'/vendor/autoload.php';
$app = require_once $applicationRoot.'/bootstrap/app.php';
$app->usePublicPath(__DIR__);
$app->handleRequest(Request::capture());
