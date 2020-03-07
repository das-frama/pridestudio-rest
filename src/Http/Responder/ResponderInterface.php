<?php

declare(strict_types=1);

namespace App\Http\Responder;

use Psr\Http\Message\ResponseInterface;

interface ResponderInterface
{
    /**
     * @param int $status
     * @param string $message
     * @param array $errors
     * @return ResponseInterface
     */
    public function error(int $status, string $message, array $errors = []): ResponseInterface;

    /**
     * @param $data
     * @param int $count
     * @param int $status
     * @return ResponseInterface
     */
    public function success($data, int $count = 1, int $status = 200): ResponseInterface;
}
