<?php

declare(strict_types=1);

namespace App\Http\Controllers\Base;

use App\Services\ValidationService;
use Psr\Http\Message\ServerRequestInterface;

trait ControllerTrait
{
    protected function getQueryParams(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $arr = array_map(function (string $str) {
            if (empty($str)) {
                return null;
            }
            if (strstr($str, ',') !== false) {
                return explode(',', $str);
            }
            if (is_numeric($str)) {
                return (int)$str;
            }
            return $str;
        }, $params);
        if (isset($arr['selected']) && !is_array($arr['selected'])) {
            $arr['selected'] = [$arr['selected']];
        }
        if (isset($arr['include']) && !is_array($arr['include'])) {
            $arr['include'] = [$arr['include']];
        }
        if (isset($arr['expand']) && !is_array($arr['expand'])) {
            $arr['expand'] = [$arr['expand']];
        }

        return $arr;
    }

    /**
     * Validate incoming request body.
     * @param ServerRequestInterface $request
     * @param array $rules
     * @return ValidationService|null
     */
    protected function validate(ServerRequestInterface $request, array $rules): ?ValidationService
    {
        $data = $request->getParsedBody();
        if (empty($data)) {
            return null;
        }

        $service = new ValidationService();
        $service->validate($data, $rules);

        return $service;
    }
}
