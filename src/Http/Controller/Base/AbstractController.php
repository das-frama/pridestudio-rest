<?php

namespace App\Http\Controller\Base;

use App\Domain\Validation\ValidationService;
use App\Exception\ValidationException;
use App\Http\Responder\ResponderInterface;
use App\Http\ValidationRequest\Base\ValidationRequestInterface;
use App\Model\Pagination;
use App\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractController
 * @package App\Http\Controller\Base
 */
abstract class AbstractController
{
    protected ResponderInterface $responder;
    protected ValidationService $validator;

    /**
     * AbstractController constructor.
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(ResponderInterface $responder, ValidationService $validator)
    {
        $this->responder = $responder;
        $this->validator = $validator;
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
        $pagination->page = $params['page'] ?? 1;
        return $pagination;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ValidationRequestInterface $validationRequest
     * @return array
     */
    protected function validateRequest(
        ServerRequestInterface $request,
        ValidationRequestInterface $validationRequest
    ): array {
        $data = $request->getParsedBody();
        if (empty($data)) {
            throw new ValidationException('Empty body.', [], ResponseFactory::BAD_REQUEST);
        }
        $errors = $this->validator->validate($data, $validationRequest->rules());
        if (count($errors) > 0) {
            throw new ValidationException('Validation error', $errors, ResponseFactory::UNPROCESSABLE_ENTITY);
        }
        return $data;
    }
}
