<?php

declare(strict_types=1);

namespace App\Http\Responder;

use App\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

class JsonResponder implements ResponderInterface
{
    public function error(int $status, string $message, array $errors = []): ResponseInterface
    {
        return ResponseFactory::fromObject($status, [
            'message' => $message,
            'errors' => $errors,
        ]);
    }

    public function success($result, int $count = null, int $status = 200): ResponseInterface
    {
        if ($count === null) {
            $count = is_array($result) ? count($result) : 1;
        }
        return ResponseFactory::fromObject($status, [
            'data' => $result,
            'count' => $count,
        ]);
    }
}
