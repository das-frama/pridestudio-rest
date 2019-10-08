<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

define('ROOT_DIR', dirname(__FILE__));
define('APP_DIR', dirname(ROOT_DIR));

(Dotenv::create(APP_DIR))->load();
