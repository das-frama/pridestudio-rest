<?php

return [
    'home' => ['GET', '/', ['app\http\controller\HomeController', 'index']],

    'booking' => ['GET', '/booking', ['app\http\controller\BookingController', 'index']],
    'booking.hall' => ['GET', '/booking/*', ['app\http\controller\BookingController', 'hall']],

    'users.all'  => ['GET', '/users', ['app\http\controller\UserController', 'all']],
    'users.read' => ['GET', '/users/*', ['app\http\controller\UserController', 'read']],
    'users.create' => ['POST', '/users', ['app\http\controller\UserController', 'create']],

    'records.price' => ['POST', '/records/price', ['app\http\controller\RecordController', 'price']],
    'records.coupon' => ['GET', '/records/coupon/*', ['app\http\controller\RecordController', 'coupon']],
    'records.read' => ['GET', '/records/*', ['app\http\controller\RecordController', 'read']],

    'halls.all'  => ['GET', '/halls', ['app\http\controller\HallController', 'all']],
    'halls.read' => ['GET', '/halls/*', ['app\http\controller\HallController', 'read']],
    'halls.services' => ['GET', '/halls/*/services', ['app\http\controller\HallController', 'services']],
    'halls.create' => ['POST', '/halls', ['app\http\controller\HallController', 'create']],
    'halls.update' => ['PUT', '/halls/*', ['app\http\controller\HallController', 'update']],
    'halls.delete' => ['DELETE', '/halls/*', ['app\http\controller\HallController', 'delete']],

    'calendar.index' => ['GET', '/calendar/*', ['app\http\controller\CalendarController', 'index']],
    'calendar.week'    => ['GET', '/calendar/*/*', ['app\http\controller\CalendarController', 'week']],
    'calendar.read'    => ['GET', '/calendar/*/*/*', ['app\http\controller\CalendarController', 'read']],

    // 'files.read' => ['GET', '/files/*/*', ['app\http\controller\FileController', 'read']],
    'files.upload' => ['POST', '/files', ['app\http\controller\FileController', 'upload']],

    'settings.all'   => ['GET', '/settings', ['app\http\controller\SettingsController', 'all']],
    'settings.group' => ['GET', '/settings/group/*', ['app\http\controller\SettingsController', 'group']],
    'settings.read'  => ['GET', '/settings/*', ['app\http\controller\SettingsController', 'read']],

    'system.init'  => ['GET', '/system/init', ['app\http\controller\SystemController', 'init']],
];
