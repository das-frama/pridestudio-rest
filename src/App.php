<?php

declare(strict_types=1);

namespace app;

use app\http\router\Router;
use app\http\router\RouterInterface;
use app\http\exception\MethodNotAllowedException;
use app\http\exception\RouteNotFoundException;
use app\http\middleware\CorsMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Dice\Dice;
use RuntimeException;

class App
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * App constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        // DI.
        $dice = new Dice();
        $dice->addRules($config['rules']);

        // Router.
        $this->router = new Router($dice);
        $this->router->load(new CorsMiddleware);
        foreach ($config['routes'] as $route) {
            $this->router->register($route[0], $route[1], $route[2]);
        }
    }

    /**
     * Run the application.
     * @param ServerRequestInterface $request
     */
    public function run(ServerRequestInterface $request): void
    {
        try {
            $response = $this->router->handle($request);
        } catch (RouteNotFoundException $e) {
            $response = ResponseFactory::fromObject(404, ['error' => 'Resource not found.']);
            $response = $response->withHeader('Access-Control-Allow-Origin', 'http://localhost:8080');
        } catch (MethodNotAllowedException $e) {
            // TODO(frama): Добавить Allow header.
            $response = ResponseFactory::fromObject(403, ['error' => 'Not allowed.']);
        } catch (RuntimeException $e) {
            $response = ResponseFactory::fromObject(500, ['error' => $e]);
        }

        $this->emit($response);
    }

    /**
     * Print response object with headers.
     * @param ResponseInterface $response
     */
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
