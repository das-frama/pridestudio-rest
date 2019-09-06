<?php

declare(strict_types=1);

namespace app\domain\validation;


class ValidationService
{
    const VALIDATION_REQUIRED = 'Отсутствует обязательное поле {property}.';
    const VALIDATION_STRING = 'Поле {property} должно быть строкой.';
    const VALIDATION_STRING_MIN = 'Минимальная длина значения {min}.';
    const VALIDATION_STRING_MAX = 'Максимальная длина значения {max}.';
    const VALIDATION_INT = 'Поле {property} должно быть числом.';
    const VALIDATION_INT_MIN = 'Минимальная длина значения {min}.';
    const VALIDATION_INT_MAX = 'Максимальная длина значения {max}.';
    const VALIDATION_ARRAY = 'Поле {property} должно быть массивом.';
    const VALIDATION_ARRAY_MIN = 'Минимальная длина массива {min}.';
    const VALIDATION_ARRAY_MAX = 'Максимальная длина массива {max}.';

    public function validate(object $entity, array $rules): array
    {
        $errors = [];
        // Parse validators.
        // $validators = [];
        foreach ($rules as $property => $rule) {
            $errors[$property] = $this->validateProperty($entity, $property, $rule);
        }

        return $errors;
    }

    public function validateArray($value, array $params): string
    {
        if (!is_array($value)) {
            return static::VALIDATION_ARRAY;
        }

        $count = count($params);
        if ($count == 1) {
            if (count($value) > $params[0]) {
                return static::VALIDATION_ARRAY_MAX;
            }
        } elseif ($count == 2) {
            if (count($value) < $params[0]) {
                return static::VALIDATION_ARRAY_MIN;
            }
            if (count($value) > $params[1]) {
                return static::VALIDATION_ARRAY_MAX;
            }
        }

        return "";
    }

    public function validateString($value, array $params): string
    {
        if (!is_string($value)) {
            return static::VALIDATION_STRING;
        }

        $count = count($params);
        if ($count == 1) {
            if (strlen($value) > $params[0]) {
                return static::VALIDATION_STRING_MAX;
            }
        } elseif ($count == 2) {
            if (strlen($value) < $params[0]) {
                return static::VALIDATION_STRING_MIN;
            }
            if (strlen($value) > $params[1]) {
                return static::VALIDATION_STRING_MAX;
            }
        }

        return "";
    }

    public function validateInt($value, array $params): string
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return static::VALIDATION_INT;
        }

        $count = count($params);
        if ($count == 1) {
            if (count($value) > $params[0]) {
                return static::VALIDATION_INT_MAX;
            }
        } elseif ($count == 2) {
            if (count($value) < $params[0]) {
                return static::VALIDATION_INT_MIN;
            }
            if (count($value) > $params[1]) {
                return static::VALIDATION_INT_MAX;
            }
        }

        return "";
    }

    private function validateProperty(object $entity, string $property, array $rules): array
    {
        // if (substr_compare($property, '$'))

        if (!isset($entity->{$property})) {
            if (in_array('required', $rules)) {
                return [static::VALIDATION_REQUIRED];
            }
            return [];
        }

        $errors = [];
        $value = $entity->{$property};
        foreach ($rules as $ruleString) {
            $ruleArr = explode(':', $ruleString);
            $params = array_slice($ruleArr, 1);
            $method = 'validate' . ucfirst($ruleArr[0]);
            if (method_exists($this, $method)) {
                $error = call_user_func([$this, $method], $value, $params);
                if (!empty($error)) {
                    $errors[$ruleArr[0]] = $error;
                }
            }
        }

        return $errors;
    }
}
