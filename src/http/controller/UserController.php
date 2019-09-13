<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\user\UserService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\JsonResponder;
use app\http\responder\ResponderInterface;
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
    private $userService;

    /** @var ResponderInterface */
    private $responder;

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
        return $this->responder->success($users);
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
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['User not found.']);
        }
        return $this->responder->success($user);
    }
}
