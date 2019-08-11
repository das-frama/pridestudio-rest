<?php

declare(strict_types=1);

use app\App;
use app\RequestFactory;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';
(new App($config))->run(RequestFactory::fromGlobals());
