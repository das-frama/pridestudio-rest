<?php

declare(strict_types=1);

namespace app\http\controller;

use app\RequestUtils;
use app\ResponseFactory;
use app\domain\user\UserService;
use app\domain\validation\ValidationService;
use app\entity\User;
use app\http\controller\base\ControllerTrait;
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

    public function __construct(UserService $userService, ResponderInterface $responder)
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
        $params = $this->getQueryParams($request);
        $users = $this->userService->findAll([], $params['include'] ?? []);
        return $this->responder->success($users);
    }

    /**
     * Read one specific user.
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request): ResponseInterface
    {
        $id = RequestUtils::getPathSegment($request, 2);
        // Validate id.
        $err = (new ValidationService)->validateMongoid($id);
        if ($err !== null) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Wrong user id.']);
        }
        // Find a user.
        $params = $this->getQueryParams($request);
        $user = $this->userService->findByID($id, $params['include'] ?? []);
        if ($user === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['User not found.']);
        }
        return $this->responder->success($user);
    }

    /**
     * Create a user.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Get body from request.
        $body = $request->getParsedBody();
        if ($body === null) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }
        $validationService = new ValidationService;
        $rules = [
            'email' => ['required', 'email:1:64'],
            'password' => ['required', 'string:6:0'],
            'name' => ['required', 'string:1:64'],
            'role' => ['required', 'string', 'enum:user,manager,admin'],
            'phone' => ['string:1:32'],
        ];
        // Sanitize incoming data.
        $body = $validationService->sanitize($body, $rules);
        // Validate data.
        $errors = $validationService->validate($body, $rules);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        // Prepare user entity.
        $user = new User;
        $user->email = $body->email;
        $user->name = $body->name;
        $user->role = $body->role;
        $user->phone = $body->phone;
        $user->setPassword($body->password);

        // Create user.
        $id = $this->userService->create($user);
        if ($id === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, ['Error during saving a record.']);
        }

        return $this->responder->success($id);
    }
}
