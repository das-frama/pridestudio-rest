#!/usr/bin/env php
<?php

declare(strict_types=1);

use app\console\App;

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/app.php';
$status = (new App($config))->run($argv);
exit($status);
