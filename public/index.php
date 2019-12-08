<?php

declare(strict_types=1);

use App\App;
use App\RequestFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

define('WEB_ROOT_DIR', dirname(__FILE__));
define('APP_DIR', dirname(WEB_ROOT_DIR));
define('HOST', getenv('APP_HOST'));

(DotEnv::create(APP_DIR))->load();
$config = require __DIR__ . '/../config/main.php';
(new App($config))->run(RequestFactory::fromGlobals());
