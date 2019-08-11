<?php

declare(strict_types=1);

namespace app;

use Psr\Http\Message\ServerRequestInterface;

class RequestUtils
{
    public static function getPathSegment(ServerRequestInterface $request, int $part): string
    {
        $path = $request->getUri()->getPath();
        $pathSegments = explode('/', rtrim($path, '/'));
        if ($part < 0 || $part >= count($pathSegments)) {
            return '';
        }
        return urldecode($pathSegments[$part]);
    }
}
