<?php

declare(strict_types=1);

namespace app\http\controller;

use app\domain\user\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Exception\RouteNotFoundException;

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
        switch ($request->getAttribute('@route')) {
            case 'user.all':
                return $this->all($request);
            case 'user.read':
                return $this->read($request);
        }

        return $handler->handle($request);
    }

    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->service->findAll(0, 0);
        $response = (new ResponseFactory())->createResponse();
        $response->getBody()->write(json_encode($users, JSON_UNESCAPED_UNICODE));
        return $response;
    }

    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $user = $this->service->findByID($id);
        if ($user === null) {
            throw new RouteNotFoundException($request);
        }
        $response = (new ResponseFactory())->createResponse();
        $response->getBody()->write(json_encode($user, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}
