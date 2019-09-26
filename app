#!/usr/bin/env php
<?php

declare(strict_types=1);

use app\console\App;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

define('APP_DIR', __DIR__);

(Dotenv::create(APP_DIR))->load();
$config = require __DIR__ . '/config/app.php';
$status = (new App($config))->run($argv);
exit($status);
