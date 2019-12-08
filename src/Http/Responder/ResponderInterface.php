<?php

declare(strict_types=1);

namespace App\Http\Responder;

use Psr\Http\Message\ResponseInterface;

interface ResponderInterface
{
    public function error(int $status, array $errors): ResponseInterface;
    public function success($result, int $count = 1, int $status = 200): ResponseInterface;
}
