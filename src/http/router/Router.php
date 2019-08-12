<?php

declare(strict_types=1);

namespace app\http\router;

use app\RequestUtils;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Dice\Dice;
use RuntimeException;

class Router implements RouterInterface
{
    private $routeHandlers = [];
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
     * 
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

    public function route(ServerRequestInterface $request): ResponseInterface
    {
        // $data = gzcompress(json_encode($this->routes, JSON_UNESCAPED_UNICODE));
        return $this->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $routeNumbers = $this->getRouteNumbers($request);
        if (count($routeNumbers) == 0) {
            throw new RouteNotFoundException();
        }

        try {
            list($class, $method) = $this->routeHandlers[$routeNumbers[0]];
            $controller = $this->dice->create($class);
            $response = call_user_func([$controller, $method], $request);
        } catch (MongoDB\Exception\Exception $e) {
            throw new RuntimeException();
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
