<?php

return [
    // Booking.
    ['GET', '/frontend/booking', ['App\Http\Controller\Frontend\BookingController', 'index']],
    ['GET', '/frontend/booking/*', ['App\Http\Controller\Frontend\BookingController', 'hall']],
    // Halls.
    ['GET', '/frontend/halls', ['App\Http\Controller\Frontend\HallController', 'all']],
    ['GET', '/frontend/halls/*', ['App\Http\Controller\Frontend\HallController', 'read']],
    ['GET', '/frontend/halls/*/services', ['App\Http\Controller\Frontend\HallController', 'services']],
    // Calendar.
    ['GET', '/frontend/calendar/*', ['App\Http\Controller\Frontend\CalendarController', 'index']],
    ['GET', '/frontend/calendar/*/*', ['App\Http\Controller\Frontend\CalendarController', 'week']],
    ['GET', '/frontend/calendar/*/*/*', ['App\Http\Controller\Frontend\CalendarController', 'read']],
    // Records.
    ['POST', '/frontend/records', ['App\Http\Controller\Frontend\RecordController', 'create']],
    ['POST', '/frontend/records/price', ['App\Http\Controller\Frontend\RecordController', 'price']],
    ['GET', '/frontend/records/coupon/*', ['App\Http\Controller\Frontend\RecordController', 'coupon']],
    // Settings.
    ['GET', '/frontend/settings', ['App\Http\Controller\Frontend\SettingsController', 'all']],
    ['GET', '/frontend/settings/*', ['App\Http\Controller\SettingsController', 'read']],
    ['GET', '/frontend/settings/group/*', ['App\Http\Controller\SettingsController', 'group']],
];
