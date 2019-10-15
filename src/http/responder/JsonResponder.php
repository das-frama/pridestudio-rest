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
            'count' => 0,
            'errors' => $errors
        ]);
    }

    public function success($result, int $count = null): ResponseInterface
    {
        if ($count === null) {
            $count = is_array($result) ? count($result) : 1;
        }
        return ResponseFactory::fromObject(200, [
            'data' => $result,
            'count' => $count,
            'errors' => []
        ]);
    }
}
