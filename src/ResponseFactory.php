<?php

declare(strict_types=1);

namespace app;

use app\entity\File;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class ResponseFactory
{
    const OK = 200;
    const CREATED = 201;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const CONFLICT = 409;
    const UNPROCESSABLE_ENTITY = 422;
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * Get response object from object (array, object, scalar).
     * @param int $status
     * @param array $body
     * @return ResponseInterface
     */
    public static function fromObject(int $status, array $body): ResponseInterface
    {
        $content = json_encode($body, JSON_UNESCAPED_UNICODE);
        return self::from($status, 'application/json', $content);
    }

    /**
     * Get response object from file.
     * @param File $file
     * @return ResponseInterface
     */
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

    /**
     * @param int $status
     * @param string $contentType
     * @param string $content
     * @return ResponseInterface
     */
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

    /**
     * Get response object with status.
     * @param int $status
     * @return ResponseInterface
     */
    public static function fromStatus(int $status): ResponseInterface
    {
        $psr17Factory = new Psr17Factory();
        return $psr17Factory->createResponse($status);
    }
}
