<?php

use App\Domain\Auth\AuthService;
use App\Domain\File\FileService;
use App\Http\Responder\JsonResponder;
use App\Http\Responder\ResponderInterface;
use MongoDB\Client;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    Client::class => [
        'constructParams' => [getenv('DB_URI')],
        'shared' => true,
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
    'App\Http\Middleware\JwtAuthMiddleware' => [
        'constructParams' => [getenv('JWT_SECRET')],
        'shared' => true,
    ],

    'App\Domain\Record\RecordRepositoryInterface' => [
        'instanceOf' => 'App\Storage\MongoDB\RecordRepository',
        'shared' => true
    ],
    'App\Domain\Client\ClientRepositoryInterface' => [
        'instanceOf' => 'App\Storage\MongoDB\ClientRepository',
        'shared' => true
    ],
    'App\Domain\Record\CouponRepositoryInterface' => [
        'instanceOf' => 'App\Storage\MongoDB\CouponRepository',
        'shared' => true
    ],
    'App\Domain\Hall\HallRepositoryInterface' => [
        'instanceOf' => 'App\Storage\MongoDB\HallRepository',
        'shared' => true
    ],
    'App\Domain\Service\ServiceRepositoryInterface' => [
        'instanceOf' => 'App\Storage\MongoDB\ServiceRepository',
        'shared' => true
    ],
    'App\Domain\Setting\SettingRepositoryInterface' => [
        'instanceOf' => 'App\Storage\MongoDB\SettingRepository',
        'shared' => true
    ],
    'App\Domain\User\UserRepositoryInterface' => [
        'instanceOf' => 'App\Storage\MongoDB\UserRepository',
        'shared' => true
    ],
];
