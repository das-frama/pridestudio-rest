<?php

declare(strict_types=1);

namespace app;

use app\http\router\Router;
use app\http\router\RouterInterface;
use app\http\middleware\CorsMiddleware;
use app\http\middleware\JwtAuthMiddleware;
use app\http\middleware\LogMiddleware;
use app\http\responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Dice\Dice;
use Error;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Exception;

class App
{
    /** @var RouterInterface */
    private $router;

    /** @var ResponderInterface */
    private $responder;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $env;

    /** @var bool */
    private $debug;

    /**
     * App constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        // DI.
        $dice = (new Dice())->addRules($config['rules']);
        // Set level mode.
        $this->env = getenv('APP_ENV');
        $this->debug = getenv('APP_DEBUG') === 'true';
        // Logger.
        $this->logger = $dice->create(LoggerInterface::class);
        $this->logger->pushHandler(
            (new StreamHandler($config['logger']['path'], $config['logger']['level']))
                ->setFormatter(new LineFormatter(null, null, true, true))
        );
        ErrorHandler::register($this->logger);
        // Responder.
        $this->responder = $dice->create(ResponderInterface::class);
        // Router.
        $this->router = new Router(getenv('APP_BASE_PATH'), $dice, $this->responder);
        // Load middlewares.
        if ($this->debug) {
            $this->router->load(new LogMiddleware($this->logger, $this->debug));
        }
        $this->router->load(new JwtAuthMiddleware($this->responder, getenv('JWT_SECRET')));
        $this->router->load(new CorsMiddleware);
        // Load routes.
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
        } catch (Exception $e) {
            $message = 'Internal Server Error';
            if ($this->debug) {
                $message = $e->getMessage();
            }
            $response = $this->responder->error(ResponseFactory::INTERNAL_SERVER_ERROR, [$message]);
            $this->emit($response);
            throw $e;
        } catch (Error $e) {
            $message = 'Internal Server Error';
            if ($this->debug) {
                $message = $e->getMessage();
            }
            $response = $this->responder->error(ResponseFactory::INTERNAL_SERVER_ERROR, [$message]);
            $this->emit($response);
            throw $e;
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

    /**
     * Add parsed to request and return it.
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
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

                    case 'multipart/form-data':
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

    /**
     * Parse JSON body to php object.
     * @param string $body
     * @return array
     */
    private function parseJSONBody(string $body): ?array
    {
        $object = json_decode($body, true);
        $err = json_last_error();
        if ($err !== JSON_ERROR_NONE) {
            $object = null;
        }

        return $object;
    }

    /**
     * Parse x-www-form-urlencoded to php object.
     * @param string $body
     * @return object
     */
    private function parseURLBody(string $body): ?object
    {
        parse_str($body, $input);
        return (object) $input;
    }
}
