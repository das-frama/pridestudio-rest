<?php

declare(strict_types=1);

namespace app\http\middleware;

use app\RequestUtils;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LogMiddleware implements MiddlewareInterface
{
    /** @var LoggerInterface */
    private $log;

    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $this->log->debug(RequestUtils::str($request));
        return $next->handle($request);
    }
}
