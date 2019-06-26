<?php declare(strict_types=1);

namespace app;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\RouterInterface;

class App
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * App constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function run(ServerRequestInterface $request) : void
    {
        try {
            $response = $this->router->handle($request);
        } catch (RouteNotFoundException $e) {
            $response = (new ResponseFactory())->createResponse(404);
            $response->getBody()->write($response->getReasonPhrase());
        } catch (MethodNotAllowedException $e) {
            $response = (new ResponseFactory())
                ->createResponse(405)
                ->withHeader("Allow", implode(", ", $e->getAllowedMethods()));
            $response->getBody()->write($response->getReasonPhrase());
        }

        $this->emit($response);
    }

    public function emit(ResponseInterface $response) : void
    {
        // Emit headers iteratively:
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        header(sprintf(
            "HTTP/%s %d %s",
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ), true);

        echo $response->getBody();
    }
}
