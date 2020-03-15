<?php
declare(strict_types=1);

/**
 * A custom PHP Web App for Pridestudio.
 *
 * @author Andrey Galaktionov <das.frama@gmail.com>
 * @version 0.1.1
 */

require __DIR__ . '/../vendor/autoload.php';

(Dotenv\DotEnv::create(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR))->load();
$config = require __DIR__ . '/../config/main.php';
(new App\App($config))->run(\App\RequestFactory::fromGlobals());
