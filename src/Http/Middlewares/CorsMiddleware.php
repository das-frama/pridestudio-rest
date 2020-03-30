<?php
declare(strict_types=1);

namespace App\Http\Middlewares;

use App\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CorsMiddleware
 * @package App\Http\Middlewares
 */
class CorsMiddleware implements MiddlewareInterface
{
    protected array $origins;

    /**
     * CorsMiddleware constructor.
     * @param array $origins
     */
    public function __construct(array $origins = [])
    {
        $this->origins = $origins;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($request);
        if ($request->getMethod() === 'OPTIONS') {
            $response = ResponseFactory::fromStatus(200);
            $response = $response->withHeader(
                'Access-Control-Allow-Headers',
                'Content-Type, X-CSRF-TOKEN, Authorization'
            );
            $response = $response->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, PUT, POST, DELETE, PATCH');
            $response = $response->withHeader('Access-Control-Max-Age', '1728000');
            $response = $response->withHeader('Access-Control-Expose-Headers', '');
        }

        return $response;
    }
}
