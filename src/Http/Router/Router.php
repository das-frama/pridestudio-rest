<?php
declare(strict_types=1);

namespace App\Http\Router;

use App\Http\Middlewares\CorsMiddleware;
use App\Http\Middlewares\JwtAuthMiddleware;
use App\Http\Middlewares\LogMiddleware;
use App\Http\Responders\ResponderInterface;
use App\RequestUtils;
use App\ResponseFactory;
use Dice\Dice;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;

/**
 * Class Router
 * @package App\Http\Router
 */
class Router implements RouterInterface
{
    private array $routeHandlers = [];
    private array $middlewares = [];
    private array $routeMiddlewares = [];
    private PathTree $routes;
    private Dice $dice;
    private ResponderInterface $responder;
    private string $basePath;

    /**
     * Router constructor.
     * @param string $basePath
     * @param Dice $dice
     * @param ResponderInterface $responder
     */
    public function __construct(string $basePath, Dice $dice, ResponderInterface $responder)
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
     * @param string $handler
     * @param array $middlewares
     */
    public function register(string $method, string $path, string $handler, array $middlewares = []): void
    {
        $routeNumber = count($this->routeHandlers);
        $this->routeHandlers[$routeNumber] = $handler;
        if (!empty($middlewares)) {
            $this->routeMiddlewares[$routeNumber] = array_map([$this, 'getMiddlewareClass'], $middlewares);
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
        // Remove base path from request.
        $request = $this->removeBasePath($request);

        // Router Middlewares.
        if (count($this->middlewares)) {
            $handler = array_pop($this->middlewares);
            return $handler->process($request, $this);
        }

        // Get route number id.
        $routeNumbers = $this->getRouteNumbers($request);
        if (count($routeNumbers) === 0) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'Route not found.');
        }

        // Route Middlewares.
        if (isset($this->routeMiddlewares[$routeNumbers[0]])) {
            if (count($this->routeMiddlewares[$routeNumbers[0]])) {
                $class = array_pop($this->routeMiddlewares[$routeNumbers[0]]);
                $handler = $this->dice->create($class);
                return $handler->process($request, $this);
            }
        }

        // Process route's controller.
        try {
            list($class, $method) = explode('@', $this->routeHandlers[$routeNumbers[0]], 2);
            $class = 'App\\Http\\Controllers\\' . $class;
            $dice = $this->dice->addRule($class, [
                'call' => [
                    [$method, [$request], Dice::CHAIN_CALL],
                ],
            ]);
            /** @var ResponseInterface $response */
            $response = $dice->create($class);
            return $response;
        } catch (\MongoDB\Exception\RuntimeException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
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

    /**
     * Get middleware class name based on short key.
     * @param string $key
     * @return string
     */
    protected function getMiddlewareClass(string $key): string
    {
        switch ($key) {
            case 'jwt':
                return JwtAuthMiddleware::class;
            case 'cors':
                return CorsMiddleware::class;
            case 'log':
                return LogMiddleware::class;
            default:
                return '';
        }
    }
}
