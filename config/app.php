<?php

return [
    'routes' => [
        ['GET', '/', ['app\http\controller\HomeController', 'index']],
        ['GET', '/users', ['app\http\controller\UserController', 'all']],
        ['GET', '/users/*', ['app\http\controller\UserController', 'read']],
    ],
];
