<?php

use app\domain\auth\AuthService;
use app\domain\file\FileService;
use app\http\responder\JsonResponder;
use app\http\responder\ResponderInterface;
use MongoDB\Client;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    Client::class => [
        'shared' => true,
        'constructParams' => [
            getenv('DB_URI'),
        ]
    ],
    FileService::class => [
        'constructParams' => [getenv('APP_STORAGE_PATH')],
    ],
    AuthService::class => [
        'constructParams' => [getenv('JWT_SECRET')],
    ],
    ResponderInterface::class => [
        'instanceOf' => JsonResponder::class,
        'shared' => true
    ],
    LoggerInterface::class => [
        'instanceOf' => Logger::class,
        'constructParams' => ['app', [], [], null],
        'shared' => true,
    ],

    'app\domain\record\RecordRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\RecordRepository',
        'shared' => true
    ],
    'app\domain\hall\HallRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\HallRepository',
        'shared' => true
    ],
    'app\domain\service\ServiceRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\ServiceRepository',
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
    'app\domain\record\CouponRepositoryInterface' => [
        'instanceOf' => 'app\storage\mongodb\CouponRepository',
        'shared' => true
    ],
];
