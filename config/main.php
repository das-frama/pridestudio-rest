<?php

use Monolog\Logger;

return [
    'name' => 'Pridestudio API',
    'version' => '0.1.1',

    'env' => getenv('APP_ENV'),
    'debug' => getenv('APP_DEBUG'),

//    'path' => realpath('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'storage'),
    'url' => getenv('APP_URL'),

    'storage' => [
        'path' => realpath('../storage/public'),
        'url' => getenv('APP_URL') . '/storage',
    ],

    'logger' => [
        'name' => 'app',
        'path' => realpath('../storage/logs/app.log'),
        'level' => Logger::WARNING,
    ],

    'rules' => require __DIR__ . '/rules.php',
    'routes' => [
        'api' => require __DIR__ . '/../routes/api.php',
        'frontend' => require __DIR__ . '/../routes/frontend.php',
    ],
];
