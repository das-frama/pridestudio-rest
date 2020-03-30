<?php

use Monolog\Logger;

return [
    'name' => 'PrideStudio',
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
        'path' => realpath('../storage/logs') . DIRECTORY_SEPARATOR . 'app.log',
        'level' => Logger::WARNING,
    ],

    'rules' => require __DIR__ . '/rules.php',
    'routes' => [
        'api' => require __DIR__ . '/../routes/api.php',
        'frontend' => require __DIR__ . '/../routes/frontend.php',
        'console' => require  __DIR__ . '/../routes/console.php',
    ],

    'cors' => [
        'origins' => [
            'http://dashboard.pridestudio.local:8080',
            'http://pridestudio.local:8080',
        ]
    ]
];
