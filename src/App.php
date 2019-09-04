<?php

declare(strict_types=1);

namespace app;

use app\http\exception\ArgumentMismatchException;
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
            $response = $this->router->handle($this->addParsedBody($request));
        } catch (RouteNotFoundException $e) {
            $response = ResponseFactory::fromObject(404, ['error' => 'Resource not found.']);
            $response = $response->withHeader('Access-Control-Allow-Origin', 'http://localhost:8080');
        } catch (MethodNotAllowedException $e) {
            // TODO(frama): Добавить Allow header.
            $response = ResponseFactory::fromObject(403, ['error' => 'Not allowed.']);
        } catch (ArgumentMismatchException $e) {
            $response = ResponseFactory::fromObject(422, ['error' => 'Argument count mismatch.']);
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

    private function addParsedBody(ServerRequestInterface $request): ServerRequestInterface
    {
        if (!$request->hasHeader('Content-Type')) {
            return $request;
        }

        $body = $request->getBody();
        if ($body->isReadable() && $body->isSeekable()) {
            $contents = $body->getContents();
            $body->rewind();
            if ($contents) {
                switch ($request->getHeaderLine('Content-Type')) {
                    case 'application/json':
                        $parsedBody = $this->parseJSONBody($contents);
                        break;

                    case 'application/x-www-form-urlencoded':
                        $parsedBody = $this->parseURLBody($contents);
                        break;

                    default:
                        return $request;
                }
                $request = $request->withParsedBody($parsedBody);
            }
        }

        return $request;
    }

    private function parseJSONBody(string $body): ?object
    {
        $object = json_decode($body);
        $err = json_last_error();
        if ($err !== JSON_ERROR_NONE) {
            $object = null;
        }

        return $object;
    }

    private function parseURLBody(string $body): ?object
    {
        parse_str($body, $input);
        return (object) $input;
    }
}
