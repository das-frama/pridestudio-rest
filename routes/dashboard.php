<?php

return [
    // Home.
    ['GET', '/', ['App\Http\Controller\HomeController', 'index']],
    // Users.
    ['GET', '/users', ['App\Http\Controller\UserController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/users/*', ['App\Http\Controller\UserController', 'read'], ['app\http\middleware\JwtAuthMiddleware']],
    ['POST', '/users', ['App\Http\Controller\UserController', 'create'], ['app\http\middleware\JwtAuthMiddleware']],
    // Records.
    ['GET', '/records', ['App\Http\Controller\RecordController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/records/*', ['App\Http\Controller\RecordController', 'read'], ['app\http\middleware\JwtAuthMiddleware']],
    [
        'GET',
        '/records/*/services',
        ['App\Http\Controller\RecordController', 'services'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    ['POST', '/records', ['App\Http\Controller\RecordController', 'create'], ['app\http\middleware\JwtAuthMiddleware']],
    [
        'PUT',
        '/records/*',
        ['App\Http\Controller\RecordController', 'update'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    [
        'DELETE',
        '/records/*',
        ['App\Http\Controller\RecordController', 'delete'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    [
        'POST',
        '/records/price',
        ['App\Http\Controller\RecordController', 'price'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    [
        'GET',
        '/records/coupon/*',
        ['App\Http\Controller\RecordController', 'coupon'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    [
        'GET',
        '/records/statuses',
        ['App\Http\Controller\RecordController', 'statuses'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    // Services.
    ['GET', '/services', ['App\Http\Controller\ServicesController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    // Halls.
    ['GET', '/halls', ['App\Http\Controller\HallController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/halls/*', ['App\Http\Controller\HallController', 'read'], ['app\http\middleware\JwtAuthMiddleware']],
    [
        'GET',
        '/halls/*/services',
        ['App\Http\Controller\HallController', 'services'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    ['POST', '/halls', ['App\Http\Controller\HallController', 'create'], ['app\http\middleware\JwtAuthMiddleware']],
    ['PUT', '/halls/*', ['App\Http\Controller\HallController', 'update'], ['app\http\middleware\JwtAuthMiddleware']],
    ['DELETE', '/halls/*', ['App\Http\Controller\HallController', 'delete'], ['app\http\middleware\JwtAuthMiddleware']],
    // Files.
    ['POST', '/files', ['App\Http\Controller\FileController', 'upload'], ['app\http\middleware\JwtAuthMiddleware']],
    // Settings.
    ['GET', '/settings', ['App\Http\Controller\SettingsController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    [
        'GET',
        '/settings/group/*',
        ['App\Http\Controller\SettingsController', 'group'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    [
        'GET',
        '/settings/*',
        ['App\Http\Controller\SettingsController', 'read'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    // System.
    [
        'GET',
        '/system/init',
        ['App\Http\Controller\SystemController', 'init'],
        ['app\http\middleware\JwtAuthMiddleware']
    ],
    // Auth.
    ['POST', '/auth/login', ['App\Http\Controller\AuthController', 'login']],
    ['GET', '/auth/me', ['App\Http\Controller\AuthController', 'me'], ['app\http\middleware\JwtAuthMiddleware']],
];
