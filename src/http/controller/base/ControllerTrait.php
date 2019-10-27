<?php

declare(strict_types=1);

namespace app\http\controller\base;

use app\ResponseFactory;
use app\domain\validation\ValidationService;
use app\storage\mongodb\base\AbstractEntity;
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
        if (isset($arr['expand']) && !is_array($arr['expand'])) {
            $arr['expand'] = [$arr['expand']];
        }
        
        return $arr;
    }

    /**
     * Validate incoming request body.
     * @param array $body
     * @return array
     */
    private function validate(array $body, array $rules): array
    {
        $validationService = new ValidationService;
        // Sanitize input.
        $body = $validationService->sanitize($body, $rules);
        // Validate input.
        return $validationService->validate($body, $rules);
    }
}
