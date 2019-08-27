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
    'routes' => [
        'home' => ['GET', '/', ['app\http\controller\HomeController', 'index']],

        'users.all' => ['GET', '/users', ['app\http\controller\UserController', 'all']],
        'users.read' => ['GET', '/users/*', ['app\http\controller\UserController', 'read']],

        'records.read' => ['GET', '/records/*', ['app\http\controller\RecordController', 'read']],

        'halls.all' => ['GET', '/halls', ['app\http\controller\HallController', 'all']],
        'halls.read' => ['GET', '/halls/*', ['app\http\controller\HallController', 'read']],

        'files.read' => ['GET', '/files/*/*/*', ['app\http\controller\FileController', 'read']]
    ],
];
