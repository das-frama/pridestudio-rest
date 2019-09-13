<?php

declare(strict_types=1);

namespace app\http\controller\base;

use app\http\responder\ResponderInterface;
use Psr\Http\Message\ServerRequestInterface;

trait ControllerTrait
{
    protected function getQueryParams(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        return array_map(function ($str) {
            return empty($str) ? [] : explode(',', $str);
        }, $params);
    }
}
