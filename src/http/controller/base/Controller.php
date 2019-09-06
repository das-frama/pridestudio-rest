<?php

declare(strict_types=1);

namespace app\http\controller\base;

use app\http\exception\BadRequestException;
use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    protected function validateRequestData(ServerRequestInterface $request, array $rules): array
    {
        // Load data.
        $body = $request->getParsedBody();
        if ($body === null) {
            throw new BadRequestException();
        }
        // Validate data.
        $validator = new ValidationService;
        return $validator->validate($body, $rules);
    }
}
