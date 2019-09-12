<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\domain\user\UserService;
use app\http\controller\base\ControllerTrait;
use app\http\exception\RouteNotFoundException;
use app\http\responder\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserController
 * @package app\http\controller
 */
class UserController
{
    use ControllerTrait;

    /** @var UserService */
    public $userService;

    public function __construct(UserService $userService, JsonResponder $responder)
    {
        $this->userService = $userService;
        $this->responder = $responder;
    }

    /**
     * Get all users.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function all(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->userService->findAll(0, 0);
        return $this->responder($users);
    }

    /**
     * Read one specific user.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        $user = $this->userService->findByID($id);
        if ($user === null) {
            throw new RouteNotFoundException();
        }
        return $this->responder($user);
    }
}
