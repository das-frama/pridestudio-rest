<?php

declare(strict_types=1);

namespace app\http\middleware;

use app\ResponseFactory;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class CorsMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $method = $request->getMethod();

        if ($method == 'OPTIONS') {
            $response = ResponseFactory::fromStatus(200);
            $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-XSRF-TOKEN, Authorization');
            $response = $response->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, PUT, POST, DELETE, PATCH');
            $response = $response->withHeader('Access-Control-Max-Age', '1728000');
            $response = $response->withHeader('Access-Control-Expose-Headers', '');
        } else {
            $response = $next->handle($request);
        }

        $response = $response->withHeader('Access-Control-Allow-Origin', 'http://localhost:8080');

        return $response;
    }
}
