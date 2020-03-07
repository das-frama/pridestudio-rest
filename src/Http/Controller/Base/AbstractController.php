<?php

namespace App\Http\Controller\Base;

use App\Domain\Validation\ValidationService;
use App\Exception\ValidationException;
use App\Http\Responder\ResponderInterface;
use App\Http\ValidationRequest\Base\ValidationRequestInterface;
use App\ResponseFactory;

/**
 * Class AbstractController
 * @package App\Http\Controller\Base
 */
abstract class AbstractController
{
    protected ResponderInterface $responder;
    private ValidationService $validator;

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
     * @param array $data
     * @param ValidationRequestInterface $validationRequest
     */
    protected function validateRequestData(array $data, ValidationRequestInterface $validationRequest): void
    {
        if (empty($data)) {
            throw new ValidationException('Empty body.', [], ResponseFactory::BAD_REQUEST);
        }
        $errors = $this->validator->validate($data, $validationRequest->rules());
        if (count($errors) > 0) {
            throw new ValidationException('Validation error', $errors, ResponseFactory::UNPROCESSABLE_ENTITY);
        }
    }
}
