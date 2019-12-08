<?php

declare(strict_types=1);

namespace App\Http\Router;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouterInterface extends RequestHandlerInterface
{
    public function register(string $method, string $path, array $handler): void;
    public function load(MiddlewareInterface $Middleware): void;
}
