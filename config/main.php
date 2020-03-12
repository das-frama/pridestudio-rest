<?php

use Monolog\Logger;

return [
    'name' => 'Pridestudio API',
    'version' => '0.1.0',
    'rules' => require __DIR__ . '/rules.php',
    'logger' => [
        'name' => 'app',
        'path' => APP_DIR . '/storage/logs/app.log',
        'level' => Logger::WARNING,
    ],
    'routes' => [
        'api' => require __DIR__ . '/../routes/api.php',
        'frontend' => require __DIR__ . '/../routes/frontend.php',
    ],
];
