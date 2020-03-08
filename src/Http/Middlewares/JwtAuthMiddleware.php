<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Repositories\UserRepositoryInterface;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class JwtAuthMiddleware
 * @package App\Http\Middleware
 */
class JwtAuthMiddleware implements MiddlewareInterface
{
    private UserRepositoryInterface $userRepo;
    private ResponderInterface $responder;
    private string $secret;

    /**
     * JwtAuthMiddleware constructor.
     * @param UserRepositoryInterface $userRepo
     * @param ResponderInterface $responder
     * @param string $secret
     */
    public function __construct(UserRepositoryInterface $userRepo, ResponderInterface $responder, string $secret)
    {
        $this->userRepo = $userRepo;
        $this->responder = $responder;
        $this->secret = $secret;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $headerValue = RequestUtils::getHeader($request, 'Authorization');
        list($type, $token) = explode(' ', trim($headerValue), 2);
        if ($type !== 'Bearer' || empty($token)) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Authentication required.');
        }

        try {
            $claims = (array)JWT::decode($token, $this->secret, ['HS256']);
        } catch (Exception $e) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Unauthorized.', (array)$e->getMessage());
        }

        $user = $this->userRepo->findOne(['id' => $claims['sub']]);
        if ($user === null) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Authentication required.');
        }
        $request = $request->withAttribute('user', $user);

        return $next->handle($request);
    }
}
