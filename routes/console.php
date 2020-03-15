<?php

return [
    'app' => [
        ['init', [], 'AppCommand@init', 'Init the app.'],
    ],
    'user' => [
        ['init', ['email', 'password'], 'UserCommand@init', 'Init super user.'],
    ]
];
