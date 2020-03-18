<?php

namespace App\Http\Controllers\Base;

use App\Exceptions\ValidationException;
use App\Http\Requests\Base\AbstractRequest;
use App\Http\Requests\Base\RequestInterface;
use App\Http\Responders\ResponderInterface;
use App\Models\Pagination;
use App\ResponseFactory;
use App\Services\ValidationService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractController
 * @package App\Http\Controllers\Base
 */
abstract class AbstractController
{
    protected ResponderInterface $responder;

    /**
     * AbstractController constructor.
     * @param ResponderInterface $responder
     */
    public function __construct(ResponderInterface $responder)
    {
        $this->responder = $responder;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $key
     * @return array
     */
    protected function getQueryParams(ServerRequestInterface $request, string $key = ""): array
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
        if ($key !== "") {
            return $arr[$key] ?? [];
        }
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
     * @param ServerRequestInterface $request
     * @return Pagination
     */
    protected function getPagination(ServerRequestInterface $request): Pagination
    {
        $params = $request->getQueryParams();
        $pagination = new Pagination();
        $pagination->query = $params['query'] ?? '';
        $pagination->limit = $params['limit'] ?? 15;
        $pagination->orderBy = $params['orderBy'] ?? '';
        $pagination->ascending = $params['ascending'] ?? 0;
        $pagination->page = empty($params['page']) ? 1 : $params['page'];
        return $pagination;
    }

//    /**
//     * @param RequestInterface $request
//     * @return RequestInterface
//     */
//    protected function validateRequest(RequestInterface $request): RequestInterface
//    {
//
//    }
}
