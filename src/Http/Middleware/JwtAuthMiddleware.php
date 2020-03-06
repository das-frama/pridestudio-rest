<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responder\ResponderInterface;
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
    private ResponderInterface $responder;
    private string $secret;

    /**
     * JwtAuthMiddleware constructor.
     * @param ResponderInterface $responder
     * @param string $secret
     */
    public function __construct(ResponderInterface $responder, string $secret)
    {
        $this->responder = $responder;
        $this->secret = $secret;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        // $path = $request->getUri()->getPath();
        // $method = $request->getMethod();
        // if ($method === 'POST' && $path === '/auth/login' || strstr($path, '/frontend') !== false) {
        //     return $next->handle($request);
        // }
        $cookies = $request->getCookieParams();
        $token = $cookies['jwt'] ?? '';
        if ($token === '') {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Authentication required.');
        }

        try {
            JWT::decode($token, $this->secret, ['HS256']);
            // CSRF validation.
            // $headerCSRF = $request->getHeader('X-CSRF-TOKEN')[0];
            // $tokenCSRF = $claims['csrf'] ?? '';
            // if (empty($headerCSRF) || empty($tokenCSRF) || !hash_equals($headerCSRF, $token)) {
            // return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Wrong or empty CSRF token.']);
            // }
        } catch (Exception $e) {
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, 'Unauthorized.', (array)$e->getMessage());
        }

        return $next->handle($request);
    }
}
