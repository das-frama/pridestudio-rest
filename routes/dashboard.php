<?php

return [
    // Home.
    ['GET', '/', 'HomeController@index'],
    // Users.
    ['GET', '/users', 'UserController@all', ['jwt']],
    ['GET', '/users/*', 'UserController@read', ['jwt']],
    ['POST', '/users', 'UserController@create', ['jwt']],
    // Records.
    ['GET', '/records', 'RecordController@all', ['jwt']],
    ['GET', '/records/*', 'RecordController@read', ['jwt']],
    ['GET', '/records/*/services', 'RecordController@services', ['jwt']],
    ['POST', '/records', 'RecordController@create', ['jwt']],
    ['PATCH', '/records/*', 'RecordController@update', ['jwt']],
    ['DELETE', '/records/*', 'RecordController@delete', ['jwt']],
    ['POST', '/records/price', 'RecordController@price', ['jwt']],
    ['GET', '/records/coupon/*', 'RecordController@coupon', ['jwt']],
    ['GET', '/records/statuses', 'RecordController@statuses', ['jwt']],
    // Services.
    ['GET', '/services', 'ServicesController@all', ['jwt']],
    // Halls.
    ['GET', '/halls', 'HallController@all', ['jwt']],
    ['GET', '/halls/*', 'HallController@read', ['jwt']],
    ['GET', '/halls/*/services', 'HallController@services', ['jwt']],
    ['POST', '/halls', 'HallController@create', ['jwt']],
    ['PUT', '/halls/*', 'HallController@update', ['jwt']],
    ['DELETE', '/halls/*', 'HallController@delete', ['jwt']],
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
    ['GET', '/auth/me', 'AuthController@me', ['jwt']],
];
