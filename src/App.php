<?php
declare(strict_types=1);

namespace App;

use App\Exceptions\ValidationException;
use App\Http\Middlewares\CorsMiddleware;
use App\Http\Middlewares\LogMiddleware;
use App\Http\Responders\ResponderInterface;
use App\Http\Router\Router;
use App\Http\Router\RouterInterface;
use Dice\Dice;
use Error;
use Exception;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class App
{
    private RouterInterface $router;
    private ResponderInterface $responder;
    private Logger $logger;
    private string $env;
    private bool $debug;

    /**
     * App constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        // DI.
        $dice = (new Dice)->addRules($config['rules']);

        // Set level mode.
        $this->env = getenv('APP_ENV');
        $this->debug = getenv('APP_DEBUG') === 'true';

        // Logger.
        $logger = $dice->create(LoggerInterface::class);
        $this->logger = $logger;
        $this->logger->pushHandler(
            (new StreamHandler($config['logger']['path'], $config['logger']['level']))
                ->setFormatter(new LineFormatter(null, null, true, true))
        );
        ErrorHandler::register($this->logger);

        // Responder.
        $responder = $dice->create(ResponderInterface::class);
        if ($responder instanceof ResponderInterface) {
            $this->responder = $responder;
        }

        // Router.
        $this->router = new Router(getenv('APP_BASE_PATH'), $dice, $this->responder);
        // Load router middlewares.
        if ($this->debug) {
            $this->router->load(new LogMiddleware($this->logger));
        }
        $this->router->load(new CorsMiddleware($config['cors']['origins']));

        // Load routes.
        $routes = array_merge($config['routes']['api'], $config['routes']['frontend']);
        foreach ($routes as $route) {
            $this->router->register($route[0], $route[1], $route[2], $route[3] ?? []);
        }

        $this->config = $config;
    }

    /**
     * Run the application.
     * @param ServerRequestInterface $request
     * @throws Exception
     */
    public function run(ServerRequestInterface $request): void
    {
        try {
            $response = $this->router->handle($this->addParsedBody($request));
            $this->emit($response);
        } catch (ValidationException $e) {
            $response = $this->responder->error($e->getCode(), $e->getMessage(), $e->getErrors());
            $this->emit($response);
        } catch (Exception $e) {
            $message = $this->env === 'production' ? 'Internal Server Error' : $e->getMessage();
            $response = $this->responder->error(ResponseFactory::INTERNAL_SERVER_ERROR, $message, (array)$e);
            $this->emit($response);
            throw $e;
        } catch (Error $e) {
            $message = $this->env === 'production' ? 'Internal Server Error' : $e->getMessage();
            $response = $this->responder->error(ResponseFactory::INTERNAL_SERVER_ERROR, $message, (array)$e);
            $this->emit($response);
            throw $e;
        }
    }

    /**
     * Add parsed to request and return it.
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    private function addParsedBody(ServerRequestInterface $request): ServerRequestInterface
    {
        $body = $request->getBody();
        if ($body->isReadable() && $body->isSeekable()) {
            $contents = $body->getContents();
            $body->rewind();
            if ($contents) {
                $request = $request->withParsedBody($this->parseJSONBody($contents));
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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function withOrigin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $origins = $this->config['cors']['origins'];
        $headers = $request->getHeaders();
        if (isset($headers['Origin']) && in_array($headers['Origin'][0], $origins)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $headers['Origin'][0]);
        }

        return $response->withHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * Parse x-www-form-urlencoded to php object.
     * @param string $body
     * @return object
     */
    private function parseURLBody(string $body): ?object
    {
        parse_str($body, $input);
        return (object)$input;
    }
}
