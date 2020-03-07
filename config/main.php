<?php

use Monolog\Logger;

return [
    'name' => 'Pridestudio REST',
    'rules' => require __DIR__ . '/rules.php',
    'logger' => [
        'name' => 'app',
        'path' => APP_DIR . '/storage/logs/app.log',
        'level' => Logger::WARNING,
    ],
    'routes' => [
        'dashboard' => require __DIR__ . '/../routes/dashboard.php',
        'frontend' => require __DIR__ . '/../routes/frontend.php',
    ],
];
