<?php

use app\domain\file\FileService;
use app\http\responder\JsonResponder;
use app\http\responder\ResponderInterface;
use MongoDB\Client;

return [
    Client::class => [
        'shared' => true,
        'constructParams' => [
            'mongodb://127.0.0.1:27017',
        ]
    ],
    FileService::class => [
        'constructParams' => ['..\storage']
    ],
    ResponderInterface::class => [
        'instanceOf' => JsonResponder::class,
        'shared' => true
    ],

    'app\domain\record\RecordRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\RecordRepository',
        'shared' => true
    ],
    'app\domain\hall\HallRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\HallRepository',
        'shared' => true
    ],
    'app\domain\setting\SettingRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\SettingRepository',
        'shared' => true
    ],
    'app\domain\user\UserRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\UserRepository',
        'shared' => true
    ],
];
