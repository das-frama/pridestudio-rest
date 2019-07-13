<?php

declare(strict_types=1);

namespace app;

use app\controller\HomeController;
use app\controller\ResourceController;
use app\storage\mysql\Storage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\RouterInterface;

class App
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * App constructor.
     */
    public function __construct()
    {
        // Database.
        $db = new Storage('mysql:host=localhost;dbname=pridestudio', 'root', '');

        // Routes.
        $routes = new RouteCollection();
        $routes->get('home', '/')
            ->addMiddleware(new HomeController);

        $routes->group('/resource', function ($routes) use ($db) {
            $service = new ResourceService($db);
            $routes->post('resource.create', '/{table}')
                ->addPattern('table', '\w+')
                ->addMiddleware(new ResourceController($service));

            $routes->patch('resource.update', '/{table}/{id}')
                ->addPattern('table', '\w+')
                ->addPattern('id', '\d+')
                ->addMiddleware(new ResourceController($service));

            $routes->delete('resource.delete', '/{table}/{id}')
                ->addPattern('table', '\w+')
                ->addPattern('id', '\d+')
                ->addMiddleware(new ResourceController($service));

            $routes->get('resource.read', '/{table}/{id}')
                ->addPattern('table', '\w+')
                ->addPattern('id', '\d+')
                ->addMiddleware(new ResourceController($service));

            $routes->get('resource.all', '/{table}')
                ->addPattern('table', '\w+')
                ->addMiddleware(new ResourceController($service));
        });

        // Router.
        $router = new Router();
        $router->addRoutes($routes);

        $this->router = $router;
    }

    public function run(ServerRequestInterface $request): void
    {
        try {
            $response = $this->router->handle($request)
                ->withHeader('Content-Type', 'application/json');
            $response->getBody()->getContents();
        } catch (RouteNotFoundException $e) {
            $response = (new ResponseFactory())->createResponse(404);
            $response->getBody()->write($response->getReasonPhrase());
        } catch (MethodNotAllowedException $e) {
            $response = (new ResponseFactory())
                ->createResponse(405)
                ->withHeader('Allow', implode(', ', $e->getAllowedMethods()));
            $response->getBody()->write($response->getReasonPhrase());
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
