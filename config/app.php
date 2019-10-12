<?php

use Monolog\Logger;

return [
    'logger' => [
        'name' => 'app',
        'path' => APP_DIR . '/storage/logs/app.log',
        'level' => Logger::DEBUG,
    ],
    'rules' => require __DIR__ . '/rules.php',
    'routes' => require __DIR__ . '/routes.php',
];
