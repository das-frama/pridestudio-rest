<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\ResponseFactory;
use App\Http\Responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Firebase\JWT\JWT;
use Exception;

class JwtAuthMiddleware implements MiddlewareInterface
{
    /** @var ResponderInterface */
    private $responder;

    /** @var string */
    private $secret;
    
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
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, ['Authentication required.']);
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
            return $this->responder->error(ResponseFactory::UNAUTHORIZED, [$e->getMessage()]);
        }

        return $next->handle($request);
    }
}
