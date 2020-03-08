<?php

use App\Http\Middlewares\JwtAuthMiddleware;
use App\Http\Responders\JsonResponder;
use App\Http\Responders\ResponderInterface;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\CouponRepositoryInterface;
use App\Repositories\HallRepositoryInterface;
use App\Repositories\MongoDB\ClientRepository;
use App\Repositories\MongoDB\CouponRepository;
use App\Repositories\MongoDB\HallRepository;
use App\Repositories\MongoDB\RecordRepository;
use App\Repositories\MongoDB\SettingRepository;
use App\Repositories\MongoDB\UserRepository;
use App\Repositories\RecordRepositoryInterface;
use App\Repositories\ServiceRepositoryInterface;
use App\Repositories\SettingRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use App\Services\FileService;
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
    JwtAuthMiddleware::class => [
        'constructParams' => [getenv('JWT_SECRET')],
        'shared' => true,
    ],
    RecordRepositoryInterface::class => [
        'instanceOf' => RecordRepository::class,
        'shared' => true
    ],
    ClientRepositoryInterface::class => [
        'instanceOf' => ClientRepository::class,
        'shared' => true
    ],
    CouponRepositoryInterface::class => [
        'instanceOf' => CouponRepository::class,
        'shared' => true
    ],
    HallRepositoryInterface::class => [
        'instanceOf' => HallRepository::class,
        'shared' => true
    ],
    ServiceRepositoryInterface::class => [
        'instanceOf' => SettingRepository::class,
        'shared' => true
    ],
    SettingRepositoryInterface::class => [
        'instanceOf' => SettingRepository::class,
        'shared' => true
    ],
    UserRepositoryInterface::class => [
        'instanceOf' => UserRepository::class,
        'shared' => true
    ],
];
