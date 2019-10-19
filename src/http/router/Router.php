<?php

declare(strict_types=1);

namespace app\http\router;

use app\RequestUtils;
use app\ResponseFactory;
use app\http\responder\JsonResponder;
use app\http\responder\ResponderInterface;
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

    /** @var ResponderInterface */
    private $responder;

    /** @var string */
    private $basePath;

    public function __construct(string $basePath, Dice $dice, JsonResponder $responder)
    {
        $this->basePath = $basePath;
        $this->dice = $dice;
        $this->responder = $responder;
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
        $this->middlewares[] = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = $this->removeBasePath($request);

        // Middlewares.
        if (count($this->middlewares)) {
            $handler = array_pop($this->middlewares);
            return $handler->process($request, $this);
        }

        $routeNumbers = $this->getRouteNumbers($request);
        if (count($routeNumbers) === 0) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Route not found.']);
        }

        try {
            list($class, $method) = $this->routeHandlers[$routeNumbers[0]];
            $controller = $this->dice->create($class);
            $response = call_user_func([$controller, $method], $request);
        } catch (\MongoDB\Exception\RuntimeException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    private function removeBasePath(ServerRequestInterface $request): ServerRequestInterface
    {
        $path = $request->getUri()->getPath();
        if (substr($path, 0, strlen($this->basePath)) == $this->basePath) {
            $path = substr($path, strlen($this->basePath));
            $request = $request->withUri($request->getUri()->withPath($path));
        }
        return $request;
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
