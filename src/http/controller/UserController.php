<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\user\UserService;
use app\storage\mongodb\UserRepository;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MongoDB\Database;

/**
 * User class.
 */
class UserController
{
    /**
     * @var UserService
     */
    private $service;

    public function __construct(Database $db)
    {
        $this->service = new UserService(new UserRepository($db));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->service->findAll(0, 0);
        return ResponseFactory::fromObject(200, $users);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $user = $this->service->findByID($id);
        if ($user === null) {
            throw new RouteNotFoundException();
        }

        return ResponseFactory::fromObject(200, $user);
    }
}
