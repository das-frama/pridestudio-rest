<?php

declare(strict_types=1);

use app\App;
use app\RequestFactory;

require __DIR__ . '/../vendor/autoload.php';

define('ROOT_DIR', dirname(__FILE__));
define('HOST', 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']);

$config = require __DIR__ . '/../config/app.php';
(new App($config))->run(RequestFactory::fromGlobals());
