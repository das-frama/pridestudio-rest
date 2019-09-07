<?php

declare(strict_types=1);

namespace app\http\controller\base;

use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    protected function getQueryParams(ServerRequestInterface $request): array
    {
        $params = [];
        $query = $request->getUri()->getQuery();
        $query = str_replace('][]=', ']=', str_replace('=', '[]=', $query));
        parse_str($query, $params);
        return $params;
    }
}
