<?php

use MongoDB\Client;
use app\domain\user\UserRepositoryInterface;
use app\storage\mongodb\UserRepository;

return [
    'rules' => [
        Client::class => [
            'shared' => true,
            'constructParams' => [
                'mongodb://127.0.0.1:27017',
            ]
        ],
    ],
    'routes' => [
        'home' => ['GET', '/', ['app\http\controller\HomeController', 'index']],
        'users.all' => ['GET', '/users', ['app\http\controller\UserController', 'all']],
        'users.read' => ['GET', '/users/*', ['app\http\controller\UserController', 'read']],
        'records.read' => ['GET', '/records/*', ['app\http\controller\RecordController', 'read']],
    ],
];
