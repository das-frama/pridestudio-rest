<?php

declare(strict_types=1);

namespace app\http\responder;

use app\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Countable;

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

    public function success($result): ResponseInterface
    {
        $count = 1;
        if (is_array($result)) {
            $count = count($result);
        }
        return ResponseFactory::fromObject(200, [
            'data' => $result,
            'count' => $count,
            'errors' => []
        ]);
    }
}
