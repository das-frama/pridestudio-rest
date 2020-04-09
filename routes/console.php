<?php

return [
    'app' => [
        ['init', [], 'AppCommand@init', 'Init the app.'],
    ],
    'coupon' => [
        ['list', [], 'CouponCommand@list', 'List new coupons.'],
        ['create', ['code', 'factor'], 'CouponCommand@create', 'Create new coupon with code and factor.'],
    ],
    'user' => [
        ['init', ['email', 'password'], 'UserCommand@init', 'Init super user.'],
    ],
];
