<?php
declare(strict_types=1);

namespace App;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;

class RequestFactory
{
    /**
     * @return ServerRequestInterface
     */
    public static function fromGlobals(): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $serverRequest = $creator->fromGlobals();
        $stream = $psr17Factory->createStreamFromFile('php://input');
        $serverRequest = $serverRequest->withBody($stream);

        return $serverRequest;
    }
}
