<?php

declare(strict_types=1);

namespace App\Http\Router;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouterInterface extends RequestHandlerInterface
{
    public function register(string $method, string $path, array $handler): void;

    public function load(MiddlewareInterface $Middleware): void;
}
