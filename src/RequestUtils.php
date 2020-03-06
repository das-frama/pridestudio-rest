<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestUtils
{
    /**
     * Get path segment of requested url. ('/users/1': users - 1, 1 - 2)
     * @param ServerRequestInterface $request
     * @param int $part
     * @return string
     */
    public static function getPathSegment(ServerRequestInterface $request, int $part): string
    {
        $path = $request->getUri()->getPath();
        $pathSegments = explode('/', rtrim($path, '/'));
        if ($part < 0 || $part >= count($pathSegments)) {
            return '';
        }
        return urldecode($pathSegments[$part]);
    }

    public static function getHeader(ServerRequestInterface $request, string $header): string
    {
        $headers = $request->getHeader($header);
        return $headers[0] ?? '';
    }

    /**
     * Implementation from Https://github.com/guzzle/psr7/blob/master/src/functions.php
     * @param MessageInterface $message
     * @return string
     */
    public static function str(MessageInterface $message): string
    {
        if ($message instanceof RequestInterface) {
            $msg = trim($message->getMethod() . ' '
                    . $message->getRequestTarget())
                . ' HTTP/' . $message->getProtocolVersion();
            if (!$message->hasHeader('host')) {
                $msg .= "\r\nHost: " . $message->getUri()->getHost();
            }
        } elseif ($message instanceof ResponseInterface) {
            $msg = 'HTTP/' . $message->getProtocolVersion() . ' '
                . $message->getStatusCode() . ' '
                . $message->getReasonPhrase();
        } else {
            throw new InvalidArgumentException('Unknown message type');
        }
        foreach ($message->getHeaders() as $name => $values) {
            $msg .= "\r\n{$name}: " . implode(', ', $values);
        }
        return "{$msg}\r\n\r\n" . $message->getBody();
    }
}
