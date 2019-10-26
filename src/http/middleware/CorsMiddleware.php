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
            $response = $response->withHeader(
                'Access-Control-Allow-Headers',
                'Content-Type, X-CSRF-TOKEN, Authorization'
            );
            $response = $response->withHeader(
                'Access-Control-Allow-Methods',
                'OPTIONS, GET, PUT, POST, DELETE, PATCH'
            );
            $response = $response->withHeader('Access-Control-Max-Age', '1728000');
            $response = $response->withHeader('Access-Control-Expose-Headers', '');
        } else {
            $response = $next->handle($request);
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
