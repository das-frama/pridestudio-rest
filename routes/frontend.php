<?php

return [
    // Booking.
//    ['GET', '/frontend/booking', 'Frontend\BookingController@index'],
//    ['GET', '/frontend/booking/*', 'Frontend\BookingController@hall'],
//    // Halls.
//    ['GET', '/frontend/halls', 'Frontend\HallController@all'],
//    ['GET', '/frontend/halls/*', 'Frontend\HallController@read'],
//    ['GET', '/frontend/halls/*/services', 'Frontend\HallController@services'],
//    // Calendar.
//    ['GET', '/frontend/calendar/*', 'Frontend\CalendarController@index'],
//    ['GET', '/frontend/calendar/*/*', 'Frontend\CalendarController@week'],
//    ['GET', '/frontend/calendar/*/*/*', 'Frontend\CalendarController@read'],

//    // Records.
    ['POST', '/frontend/records', 'Frontend\RecordController@create'],
    ['POST', '/frontend/records/price', 'Frontend\RecordController@price'],
    ['GET', '/frontend/records/coupon/*', 'Frontend\RecordController@coupon'],

//    // Settings.
//    ['GET', '/frontend/settings', 'Frontend\SettingsController@all'],
//    ['GET', '/frontend/settings/*', 'Frontend\SettingsController@read'],
//    ['GET', '/frontend/settings/group/*', 'Frontend\SettingsController@group'],
];
