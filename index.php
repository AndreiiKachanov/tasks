<?php

use App\Router\Router;

require_once 'config.php';
require __DIR__ . '/vendor/autoload.php';

session_start();
$rout = new Router($_GET['q']);
$rout->request();
