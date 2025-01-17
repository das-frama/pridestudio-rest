<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\User;
use App\Http\Controllers\Base\ControllerTrait;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use App\Services\UserService;
use App\Services\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController
{
    use ControllerTrait;

    private UserService $userService;
    private ResponderInterface $responder;

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
        $err = (new ValidationService)->validateObjectId($id);
        if ($err !== []) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, 'Wrong user id.');
        }
        // Find a user.
        $params = $this->getQueryParams($request);
        $user = $this->userService->findByID($id, $params['include'] ?? []);
        if ($user === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'User not found.');
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
        // Get data from request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, 'Empty body.');
        }

        // Validate data.
        $errors = (new ValidationService)->validate($data, [
            'email' => ['required', 'email:1:64'],
            'password' => ['required', 'string:6:0'],
            'name' => ['required', 'string:1:64'],
            'role' => ['required', 'string', 'enum:user,manager,admin'],
            'phone' => ['string:1:32'],
        ]);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Unprocessable entity.', $errors);
        }

        // Prepare user Entity.
        $user = new User;
        $user->load($data, ['email', 'name', 'role', 'phone', 'password']);

        // Create user.
        $user = $this->userService->create($user);
        if ($user === null) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, 'Error during saving a record.');
        }

        return $this->responder->success($user, 1);
    }
}
