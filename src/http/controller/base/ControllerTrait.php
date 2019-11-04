<?php

declare(strict_types=1);

namespace app\http\controller\base;

use app\domain\validation\ValidationService;
use Psr\Http\Message\ServerRequestInterface;

trait ControllerTrait
{
    private function getQueryParams(ServerRequestInterface $request): array
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
                return (int) $str;
            }
            return $str;
        }, $params);
        if (isset($arr['selected']) && !is_array($arr['selected'])) {
            $arr['selected'] = [$arr['selected']];
        }
        if (isset($arr['include']) && !is_array($arr['include'])) {
            $arr['include'] = [$arr['include']];
        }
        
        return $arr;
    }

    /**
     * Validate incoming request body.
     * @param array $body
     * @return array
     */
    private function validate(ServerRequestInterface $request, array $rules): ?ValidationService
    {
        $data = $request->getParsedBody();
        if (empty($data)) {
            return null;
        }

        $service = new ValidationService($data, $rules);
        $service->validate($rules);

        return $service;
    }
}
