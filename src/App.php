<?php

declare(strict_types=1);

namespace app;

use app\http\router\Router;
use app\http\router\RouterInterface;
use app\http\exception\MethodNotAllowedException;
use app\http\exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use MongoDB\Client;

class App
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * App constructor.
     */
    public function __construct(array $config)
    {
        // MongoDB.
        $db = (new Client('mongodb://127.0.0.1:27017'))->selectDatabase('pridestudio');

        // Router.
        $this->router = new Router($db);
        foreach ($config['routes'] as $route) {
            $this->router->register($route[0], $route[1], $route[2]);
        }
    }

    public function run(ServerRequestInterface $request): void
    {
        try {
            $response = $this->router->handle($request);
        } catch (RouteNotFoundException $e) {
            $response = ResponseFactory::fromObject(404, ['error' => 'Not found.']);
        } catch (MethodNotAllowedException $e) {
            // TODO(frama): Добавить Allow header.
            $response = ResponseFactory::fromObject(403, ['error' => 'Not allowed.']);
        } catch (RuntimeException $e) {
            $response = ResponseFactory::fromObject(500, ['error' => 'Internal server error.']);
        }

        $this->emit($response);
    }

    public function emit(ResponseInterface $response): void
    {
        // Emit headers iteratively:
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        header(sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ), true);

        echo $response->getBody();
    }
}
