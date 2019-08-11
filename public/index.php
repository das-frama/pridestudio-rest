<?php

declare(strict_types=1);

use app\App;
use app\RequestFactory;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

// $start = microtime(true);
(new App($config))->run(RequestFactory::fromGlobals());
// $end = microtime(true);
// $elapsed = ($end - $start) * 1000;
// header("X-Elapsed: {$elapsed}ms");
