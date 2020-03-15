<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Base\AbstractController;
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
    /**
     * Login.
     * POST /auth/login
     * @method POST
     * @param AuthService $service
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function login(AuthService $service, ServerRequestInterface $request): ResponseInterface
    {
        // Get body from request.
        $data = $this->validateRequest($request, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Login.
        $token = $service->login($data['email'], $data['password']);
        if ($token === null) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Wrong credentials', [
                'email' => 'Wrong username or password.',
            ]);
        }

        $refreshExpiresIn = time() + 3600 * 24 * 30;
        setcookie('refresh_token', $token->refresh_token, $refreshExpiresIn, '/', 'pridestudio.local', false, true);
        return $this->responder->success($token);
    }

    /**
     * Get a new access token by refresh token.
     * @param AuthService $service
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function refresh(AuthService $service, ServerRequestInterface $request): ResponseInterface
    {
//        $refresh = RequestUtils::getPathSegment($request, 3);
        $cookie = $request->getCookieParams();
        if (!isset($cookie['refresh_token'])) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, 'Empty refresh token.');
        }
        $refresh = $cookie['refresh_token'];
        $token = $service->refresh($refresh);
        if ($token === null) {
            return $this->responder->error(ResponseFactory::FORBIDDEN, 'Refresh token not found.');
        }

        $refreshExpiresIn = time() + 3600 * 24 * 30;
        setcookie('refresh_token', $token->refresh_token, $refreshExpiresIn, '/', 'pridestudio.local', false, true);
        return $this->responder->success($token);
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
