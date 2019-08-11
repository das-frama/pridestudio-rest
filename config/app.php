<?php

return [
    'routes' => [
        'home' => ['GET', '/', ['app\http\controller\HomeController', 'index']],
        'users.all' => ['GET', '/users', ['app\http\controller\UserController', 'all']],
        'users.read' => ['GET', '/users/*', ['app\http\controller\UserController', 'read']],
    ],
];
