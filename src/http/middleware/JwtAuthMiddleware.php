<?php

declare(strict_types=1);

namespace app\http\middleware;

use app\RequestUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $token = $this->getAuthToken($request);
        if ($token) {
            $claims = $this->getClaims($token);
            $_SESSION['claims'] = $claims;
        }
    }

    private function getAuthToken(ServerRequestInterface $request): string
    {
        $headerValue = RequestUtils::getHeader($request, 'Authorization');
        $parts = explode(' ', $headerValue, 2);
        if (count($parts) !== 2) {
            return '';
        }
        if ($parts[0] !== 'Bearer') {
            return '';
        }
        return $parts[1];
    }
}
