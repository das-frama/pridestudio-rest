<?php

declare(strict_types=1);

namespace app;

use app\entity\File;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class ResponseFactory
{
    public static function fromObject(int $status, $body): ResponseInterface
    {
        $content = json_encode($body, JSON_UNESCAPED_UNICODE);
        return self::from($status, 'application/json', $content);
    }

    public static function fromFile(File $file): ResponseInterface
    {
        $psr17Factory = new Psr17Factory();
        $response = $psr17Factory->createResponse(200);
        $stream = $psr17Factory->createStreamFromFile($file->path);
        $stream->rewind();
        $response = $response->withBody($stream);
        $response = $response->withHeader('Content-Type', $file->mimeType);
        $response = $response->withHeader('Content-Length', (string) filesize($file->path));
        return $response;
    }

    private static function from(int $status, string $contentType, string $content): ResponseInterface
    {
        $psr17Factory = new Psr17Factory();
        $response = $psr17Factory->createResponse($status);
        $stream = $psr17Factory->createStream($content);
        $stream->rewind();
        $response = $response->withBody($stream);
        $response = $response->withHeader('Content-Type', $contentType);
        $response = $response->withHeader('Content-Length', strlen($content));
        return $response;
    }

    public static function fromStatus(int $status): ResponseInterface
    {
        $psr17Factory = new Psr17Factory();
        return $psr17Factory->createResponse($status);
    }
}
