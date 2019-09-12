<?php

declare(strict_types=1);

namespace app\http\responder;

use app\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

class JsonResponder implements ResponderInterface
{
    public function error(int $status, array $errors): ResponseInterface
    {
        return ResponseFactory::fromObject($status, [
            'data' => [],
            'errors' => $errors
        ]);
    }

    public function success($result): ResponseInterface
    {
        return ResponseFactory::fromObject(200, [
            'data' => $result,
            'errors' => []
        ]);
    }
}
