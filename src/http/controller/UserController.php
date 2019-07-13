<?php

declare(strict_types=1);

namespace app\http\controller;

use app\domain\user\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User class.
 */
class UserController implements MiddlewareInterface
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $users = $this->service->findAll(0, 0);

        $response = $handler->handle($request);
        $response->getBody()->write(json_encode($users, JSON_UNESCAPED_SLASHES));

        return $response;
    }
}
