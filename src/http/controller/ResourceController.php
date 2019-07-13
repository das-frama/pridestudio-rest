<?php

declare(strict_types=1);

namespace app\http\controller;

use app\ResourceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User class.
 */
class ResourceController implements MiddlewareInterface
{
    public $service;

    public function __construct(ResourceService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();
        if ($method === 'GET') {
            $result = $this->list($request);
            $response = $handler->handle($request);
            $response->getBody()->write(json_encode($result));

            return $response;
        }

        return $handler->handle($request);
    }

    public function list(ServerRequestInterface $request): array
    {
        $table = $request->getAttribute('table');
        return $this->service->list($table);
    }
}
