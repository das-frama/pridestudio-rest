<?php

return [
    // Booking..
    ['GET', '/frontend/booking', ['app\http\controller\frontend\BookingController', 'index']],
    ['GET', '/frontend/booking/*', ['app\http\controller\frontend\BookingController', 'hall']],
    // Halls.
    ['GET', '/frontend/halls', ['app\http\controller\frontend\HallController', 'all']],
    ['GET', '/frontend/halls/*', ['app\http\controller\frontend\HallController', 'read']],
    ['GET', '/frontend/halls/*/services', ['app\http\controller\frontend\HallController', 'services']],
    // Calendar.
    ['GET', '/frontend/calendar/*', ['app\http\controller\frontend\CalendarController', 'index']],
    ['GET', '/frontend/calendar/*/*', ['app\http\controller\frontend\CalendarController', 'week']],
    ['GET', '/frontend/calendar/*/*/*', ['app\http\controller\frontend\CalendarController', 'read']],
    // Records.
    ['POST', '/frontend/records', ['app\http\controller\frontend\RecordController', 'create']],
    ['POST', '/frontend/records/price', ['app\http\controller\frontend\RecordController', 'price']],
    ['GET', '/frontend/records/coupon/*', ['app\http\controller\frontend\RecordController', 'coupon']],
    // Settings.
    ['GET', 'frontend/settings', ['app\http\controller\frontend\SettingsController', 'index']],
];
