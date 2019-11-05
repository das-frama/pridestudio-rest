<?php

return [
    // Home.
    ['GET', '/', ['app\http\controller\HomeController', 'index']],
    // Users.
    ['GET', '/users', ['app\http\controller\UserController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/users/*', ['app\http\controller\UserController', 'read'], ['app\http\middleware\JwtAuthMiddleware']],
    ['POST', '/users', ['app\http\controller\UserController', 'create'], ['app\http\middleware\JwtAuthMiddleware']],
    // Records.
    ['POST', '/records', ['app\http\controller\RecordController', 'create'], ['app\http\middleware\JwtAuthMiddleware']],
    ['POST', '/records/price', ['app\http\controller\RecordController', 'price'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/records/coupon/*', ['app\http\controller\RecordController', 'coupon'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/records/statuses', ['app\http\controller\RecordController', 'statuses'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/records/*', ['app\http\controller\RecordController', 'read'], ['app\http\middleware\JwtAuthMiddleware']],
    // Services.
    ['GET', '/services', ['app\http\controller\ServicesController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    // Halls.
    ['GET', '/halls', ['app\http\controller\HallController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/halls/*', ['app\http\controller\HallController', 'read'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/halls/*/services', ['app\http\controller\HallController', 'services'], ['app\http\middleware\JwtAuthMiddleware']],
    ['POST', '/halls', ['app\http\controller\HallController', 'create'], ['app\http\middleware\JwtAuthMiddleware']],
    ['PUT', '/halls/*', ['app\http\controller\HallController', 'update'], ['app\http\middleware\JwtAuthMiddleware']],
    ['DELETE', '/halls/*', ['app\http\controller\HallController', 'delete'], ['app\http\middleware\JwtAuthMiddleware']],
    // Files.
    ['POST', '/files', ['app\http\controller\FileController', 'upload'], ['app\http\middleware\JwtAuthMiddleware']],
    // Settings.
    ['GET', '/settings', ['app\http\controller\SettingsController', 'all'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/settings/group/*', ['app\http\controller\SettingsController', 'group'], ['app\http\middleware\JwtAuthMiddleware']],
    ['GET', '/settings/*', ['app\http\controller\SettingsController', 'read'], ['app\http\middleware\JwtAuthMiddleware']],
    // System.
    ['GET', '/system/init', ['app\http\controller\SystemController', 'init'], ['app\http\middleware\JwtAuthMiddleware']],
    // Auth.
    ['POST', '/auth/login', ['app\http\controller\AuthController', 'login']],
    ['GET', '/me', ['app\http\controller\AuthController', 'me'], ['app\http\middleware\JwtAuthMiddleware']],
];
