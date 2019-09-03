<?php

return [
    'home' => ['GET', '/', ['app\http\controller\HomeController', 'index']],

    'users.all'  => ['GET', '/users', ['app\http\controller\UserController', 'all']],
    'users.read' => ['GET', '/users/*', ['app\http\controller\UserController', 'read']],

    'records.read' => ['GET', '/records/*', ['app\http\controller\RecordController', 'read']],

    'halls.all'  => ['GET', '/halls', ['app\http\controller\HallController', 'all']],
    'halls.read' => ['GET', '/halls/*', ['app\http\controller\HallController', 'read']],

    'calendar.index' => ['GET', '/calendar/*', ['app\http\controller\CalendarController', 'index']],
    'calendar.week'    => ['GET', '/calendar/*/*', ['app\http\controller\CalendarController', 'week']],
    'calendar.read'    => ['GET', '/calendar/*/*/*', ['app\http\controller\CalendarController', 'read']],

    'files.read' => ['GET', '/files/*/*/*', ['app\http\controller\FileController', 'read']],

    'settings.all'   => ['GET', '/settings', ['app\http\controller\SettingsController', 'all']],
    'settings.group' => ['GET', '/settings/group/*', ['app\http\controller\SettingsController', 'group']],
    'settings.read'  => ['GET', '/settings/*', ['app\http\controller\SettingsController', 'read']],

    'system.init'  => ['GET', '/system/init', ['app\http\controller\SystemController', 'init']],
];
