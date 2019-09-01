<?php

declare(strict_types=1);

namespace app\http\router;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RouterInterface extends RequestHandlerInterface
{
    public function register(string $method, string $path, array $handler): void;
    public function load(MiddlewareInterface $middleware): void;
    public function route(ServerRequestInterface $request): ResponseInterface;
}
