<?php

declare(strict_types=1);

namespace app\http\router;

use app\RequestUtils;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Dice\Dice;
use RuntimeException;

/**
 * Class Router
 * @package app\http\router
 */
class Router implements RouterInterface
{
    /** @var array  */
    private $routeHandlers = [];

    /** @var array  */
    private $middlewares = [];

    /** @var PathTree */
    private $routes;

    /** @var Dice */
    private $dice;

    public function __construct(Dice $dice)
    {
        $this->dice = $dice;
        $this->routes = $this->loadPathTree();
    }

    public function loadPathTree(): PathTree
    {
        // TODO (frama): Добавить кэш.
        return new PathTree;
    }

    /**
     * Register a route.
     * @param string $method
     * @param string $path
     * @param array $handler
     */
    public function register(string $method, string $path, array $handler): void
    {
        $routeNumber = count($this->routeHandlers);
        $this->routeHandlers[$routeNumber] = $handler;
        $path = trim($path, '/');
        $parts = [];
        if ($path) {
            $parts = explode('/', $path);
        }
        array_unshift($parts, $method);
        $this->routes->put($parts, $routeNumber);
    }

    /**
     * Load a middleware.
     * @param MiddlewareInterface $middleware
     */
    public function load(MiddlewareInterface $middleware): void
    {
        array_push($this->middlewares, $middleware);
    }

    public function route(ServerRequestInterface $request): ResponseInterface
    {
        // $data = gzcompress(json_encode($this->routes, JSON_UNESCAPED_UNICODE));
        return $this->handle($request);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // middlewares.
        if (count($this->middlewares)) {
            $handler = array_pop($this->middlewares);
            return $handler->process($request, $this);
        }

        $routeNumbers = $this->getRouteNumbers($request);
        if (count($routeNumbers) == 0) {
            throw new RouteNotFoundException();
        }

        try {
            list($class, $method) = $this->routeHandlers[$routeNumbers[0]];
            $controller = $this->dice->create($class);
            $response = call_user_func([$controller, $method], $request);
        } catch (\MongoDB\Exception\RuntimeException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    private function getRouteNumbers(ServerRequestInterface $request): array
    {
        $method = strtoupper($request->getMethod());
        $path = [];
        $segment = $method;
        for ($i = 1; $segment; $i++) {
            array_push($path, $segment);
            $segment = RequestUtils::getPathSegment($request, $i);
        }
        return $this->routes->match($path);
    }
}
