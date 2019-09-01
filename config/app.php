<?php

use app\domain\file\FileService;
use MongoDB\Client;

return [
    'rules' => [
        Client::class => [
            'shared' => true,
            'constructParams' => [
                'mongodb://127.0.0.1:27017',
            ]
        ],
        FileService::class => [
            'constructParams' => ['..\storage']
        ],
    ],
    'routes' => require __DIR__ . '/routes.php',
];
