<?php

return [
    // Home.
    ['GET', '/', 'HomeController@index'],

    // Users.
    ['GET', '/users', 'UserController@all', ['jwt']],
    ['GET', '/users/*', 'UserController@read', ['jwt']],
    ['POST', '/users', 'UserController@create', ['jwt']],

    // Records.
    ['RESOURCE', '/records', 'RecordController', ['jwt']],
    ['GET', '/records/*/services', 'RecordController@services', ['jwt']],
    ['POST', '/records/price', 'RecordController@price', ['jwt']],
    ['GET', '/records/coupon/*', 'RecordController@coupon', ['jwt']],
    ['GET', '/records/statuses', 'RecordController@statuses', ['jwt']],

    // Clients.
    ['RESOURCE', '/clients', 'ClientController', ['jwt']],

    // Halls.
    ['RESOURCE', '/halls', 'HallController', ['jwt']],
    ['GET', '/halls/*/services', 'HallController@services', ['jwt']],
//    // Coupons.
//    ['RESOURCE', '/coupons', 'CouponController', ['jwt']],

    // Services.
    ['GET', '/services', 'ServicesController@all', ['jwt']],

    // Files.
    ['POST', '/files', 'FileController@upload', ['jwt']],
    // Settings.
    ['GET', '/settings', 'SettingsController@all', ['jwt']],
    ['GET', '/settings/group/*', 'SettingsController@group', ['jwt']],
    ['GET', '/settings/*', 'SettingsController@read', ['jwt']],
    // System.
    ['GET', '/system/init', 'SystemController@init', ['jwt']],

    // Auth.
    ['POST', '/auth/login', 'AuthController@login'],
    ['POST', '/auth/logout', 'AuthController@logout', ['jwt']],
    ['POST', '/auth/refresh', 'AuthController@refresh'],
    ['GET', '/auth/me', 'AuthController@me', ['jwt']],
];
