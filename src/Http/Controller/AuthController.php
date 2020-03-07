<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Auth\AuthService;
use App\Http\Controller\Base\AbstractController;
use App\Http\ValidationRequest\Auth\LoginValidationRequest;
use App\ResponseFactory;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AuthController
 * @package App\Controller
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
        $data = $this->validateRequest($request, new LoginValidationRequest());

        // Login.
        $user = $service->login($data['email'], $data['password']);
        if ($user === null) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Wrong credentials', [
                'email' => 'Wrong username or password.',
            ]);
        }

        // Set JWT.
        $expiresIn = time() + (int)getenv('JWT_DURATION');
        $jwt = $service->getToken($user, $expiresIn);

        return $this->responder->success($jwt, 1);
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
