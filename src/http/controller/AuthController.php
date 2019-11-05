<?php

declare(strict_types=1);

namespace app\http\controller;

use app\ResponseFactory;
use app\domain\auth\AuthService;
use app\http\controller\base\ControllerTrait;
use app\http\responder\ResponderInterface;
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
        // Login.
        $expiresAt = time() + (int) getenv('JWT_DURATION');
        $data = $this->authService->login($body['username'], $body['password'], $expiresAt);
        if (empty($data)) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, ['Wrong username or password.']);
        }

        list($csrf, $jwt) = $data;
        // Set Cookie.
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        setcookie('jwt', $jwt, [
            'expires' => $expiresAt,
            'path' => '/',
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Strict',
        ]);
        return $this->responder->success($csrf, 1);
    }

    /**
     * Get authenticated user.
     * GET /me
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function me(ServerRequestInterface $request): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        if (!isset($cookies['jwt'])) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, ['Empty JWT.']);
        }
        $user = $this->authService->getUserByJWT($cookies['jwt']);
        if ($user === null) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['User not found.']);
        }

        return $this->responder->success($user, 1);
    }
}
