<?php

declare(strict_types=1);

namespace app\http\controller\base;

use app\ResponseFactory;
use app\domain\validation\ValidationService;
use Psr\Http\Message\ServerRequestInterface;

trait ControllerTrait
{
    private function getQueryParams(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        return array_map(function ($str) {
            if (empty($str)) {
                return null;
            }
            if (strstr($str, ',') !== false) {
                return explode(',', $str);
            }
            if (is_numeric($str)) {
                return (int) $str;
            }
            return $str;
        }, $params);
    }

    /**
     * Validate incoming request body.
     * @param ServerRequestInterface $request
     * @return object
     */
    private function validate(ServerRequestInterface $request, array $rules): object
    {
        // Get body from request.
        $body = $request->getParsedBody();
        if ($body === null) {
            return $this->responder->error(ResponseFactory::BAD_REQUEST, ['Empty body.']);
        }
        $validationService = new ValidationService;
        // Sanitize input.
        $body = $validationService->sanitize($body, $rules);
        // Validate input.
        $errors = $validationService->validate($body, $rules);
        if ($errors !== []) {
            return $this->responder->error(ResponseFactory::UNPROCESSABLE_ENTITY, $errors);
        }

        return $body;
    }
}
