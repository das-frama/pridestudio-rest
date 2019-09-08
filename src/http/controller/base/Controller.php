<?php

declare(strict_types=1);

namespace app\http\controller\base;

use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    protected function getQueryParams(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        return array_map(function ($str) {
            return explode(',', $str);
        }, $params);
    }
}
