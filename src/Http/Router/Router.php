<?php

declare(strict_types=1);

namespace App\Http\Router;

use App\RequestUtils;
use App\ResponseFactory;
use App\Http\Responder\JsonResponder;
use App\Http\Responder\ResponderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Dice\Dice;
use RuntimeException;

/**
 * Class Router
 * @package App\Http\Router
 */
class Router implements RouterInterface
{
    /** @var array  */
    private $routeHandlers = [];

    /** @var array  */
    private $Middlewares = [];

    /** @var array */
    private $routeMiddlewares = [];

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
     * @param array $Middlewares
     */
    public function register(string $method, string $path, array $handler, array $Middlewares = []): void
    {
        $routeNumber = count($this->routeHandlers);
        $this->routeHandlers[$routeNumber] = $handler;
        if (!empty($Middlewares)) {
            $this->routeMiddlewares[$routeNumber] = $Middlewares;
        }
        $path = trim($path, '/');
        $parts = [];
        if ($path) {
            $parts = explode('/', $path);
        }
        array_unshift($parts, $method);
        $this->routes->put($parts, $routeNumber);
    }

    /**
     * Load a Middleware.
     * @param MiddlewareInterface $Middleware
     */
    public function load(MiddlewareInterface $Middleware): void
    {
        $this->Middlewares[] = $Middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Remove base path from request.
        $request = $this->removeBasePath($request);

        // Router Middlewares.
        if (count($this->Middlewares)) {
            $handler = array_pop($this->Middlewares);
            return $handler->process($request, $this);
        }

        // Get route number id.
        $routeNumbers = $this->getRouteNumbers($request);
        if (count($routeNumbers) === 0) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, ['Route not found.']);
        }

        // Route Middlewares.
        if (isset($this->routeMiddlewares[$routeNumbers[0]])) {
            if (count($this->routeMiddlewares[$routeNumbers[0]])) {
                $class = array_pop($this->routeMiddlewares[$routeNumbers[0]]);
                $handler = $this->dice->create($class);
                return $handler->process($request, $this);
            }
        }

        // Proccess route's controller.
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
