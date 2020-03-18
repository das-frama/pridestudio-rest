<?php
declare(strict_types=1);

namespace App\Http\Requests\Base;

use App\Exceptions\ValidationException;
use App\ResponseFactory;
use App\Services\ValidationService;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractRequest
 * @package App\Http\Requests\Base
 */
abstract class AbstractRequest implements RequestInterface
{
    protected ServerRequestInterface $request;

    /**
     * AbstractRequest constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        // Validate request.
        $data = $request->getParsedBody();
        if (empty($data)) {
            throw new ValidationException('Empty body', [], ResponseFactory::BAD_REQUEST);
        }
        $rules = $this->rules();
        $data = array_filter($data, fn($key) => isset($rules[$key]), ARRAY_FILTER_USE_KEY);
        $errors = (new ValidationService())->validate($data, $rules);
        if (count($errors) > 0) {
            throw new ValidationException('Validation error', $errors, ResponseFactory::UNPROCESSABLE_ENTITY);
        }
        // Load data.
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        $this->request = $request;
    }

    /**
     * @return array|\string[][]
     */
    abstract public function rules(): array;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        $reflectionProperties = (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($reflectionProperties as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            if ($reflectionProperty->isInitialized($this)) {
                $array[$name] = $reflectionProperty->getValue($this);
            }
        }
        return $array;
    }

    /**
     * @return ServerRequestInterface
     */
    public function serverRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function required(): array
    {
        return [];
    }
}