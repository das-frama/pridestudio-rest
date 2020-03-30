<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Base\AbstractController;
use App\Http\Requests\Login\LoginRequest;
use App\Http\Responders\ResponderInterface;
use App\ResponseFactory;
use App\Services\AuthService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthController
 * @package App\Controllers
 */
class AuthController extends AbstractController
{
    protected AuthService $service;

    /**
     * AuthController constructor.
     * @param AuthService $service
     * @param ResponderInterface $responder
     */
    public function __construct(AuthService $service, ResponderInterface $responder)
    {
        parent::__construct($responder);
        $this->service = $service;
    }

    /**
     * Login.
     * POST /auth/login
     * @method POST
     * @param LoginRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function login(LoginRequest $request): ResponseInterface
    {
        // Login.
        $token = $this->service->login($request->email, $request->password);
        if ($token === null) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Wrong credentials', [
                'email' => 'Wrong username or password.',
            ]);
        }
        // Refresh token.
        $refreshExpiresIn = time() + 3600 * 24 * 30;
        setcookie('refresh_token', $token->refresh_token, $refreshExpiresIn, '/', getenv('COOKIE_DOMAIN'), false, true);
        return $this->responder->success($token);
    }

    /**
     * Get a new access token by refresh token.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function refresh(ServerRequestInterface $request): ResponseInterface
    {
        // Check if refresh token is present.
        $cookie = $request->getCookieParams();
        if (!isset($cookie['refresh_token'])) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, 'Empty refresh token.');
        }
        // Issue new token.
        $refresh = $cookie['refresh_token'];
        $token = $this->service->refresh($refresh);
        if ($token === null) {
            return $this->responder->error(ResponseFactory::FORBIDDEN, 'Refresh token not found.');
        }
        // Response.
        $refreshExpiresIn = time() + 3600 * 24 * 30;
        setcookie('refresh_token', $token->refresh_token, $refreshExpiresIn, '/', getenv('COOKIE_DOMAIN'), false, true);
        return $this->responder->success($token);
    }

    /**
     * Logout user from app.
     * @method POST
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $cookie = $request->getCookieParams();
        if (isset($cookie['refresh_token'])) {
            $this->service->clearRefreshToken($cookie['refresh_token']);
        }
        // Clear cookie.
        setcookie('refresh_token', '', -1, '/', getenv('COOKIE_DOMAIN'));
        return $this->responder->success(true);
    }

    /**
     * Get authenticated user.
     * GET /auth/me
     * @method GET
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function me(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responder->success($request->getAttribute('user'));
    }
}
