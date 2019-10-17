<?php

declare(strict_types=1);

namespace app\http\controller;

use app\domain\auth\AuthService;
use app\domain\validation\ValidationService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
use app\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthController
 * @package app\controller
 */
class AuthController
{
    use ControllerTrait;

    /** @var ResponderInterface */
    private $responder;

    /** @var AuthService */
    private $authService;

    public function __construct(AuthService $authService, ResponderInterface $responder)
    {
        $this->authService = $authService;
        $this->responder = $responder;
    }

    /**
     * Login.
     * POST /auth/login
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        // Get body from request.
        $body = $request->getParsedBody();
        if (empty($body)) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }
        $validationService = new ValidationService;
        $rules = [
            'username' => ['required', 'string:1:64'],
            'password' => ['required', 'string:1:64'],
        ];
        // Sanitize incoming data.
        $body = $validationService->sanitize($body, $rules);
        // Validate data.
        $errors = $validationService->validate($body, $rules);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }
        
        // Login.
        $token = $this->authService->login($body->username, $body->password);
        if ($token === null) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, ['Wrong username or password.']);
        }
        
        return $this->responder->success(['token' => $token]);
    }
}
