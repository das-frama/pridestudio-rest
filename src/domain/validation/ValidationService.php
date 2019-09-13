<?php

declare(strict_types=1);

namespace app\domain\validation;

use Mongodb\BSON\ObjectId;

class ValidationService
{
    const VALIDATION_REQUIRED = "Отсутствует обязательное поле '%s'.";
    const VALIDATION_STRING = "Поле '%s' должно быть строкой.";
    const VALIDATION_STRING_MIN = "Минимальная допустимая длина строки должна быть не меньше %d символов.";
    const VALIDATION_STRING_MAX = "Максимальная допустимая длина строки должна быть не больше %d символов.";
    const VALIDATION_INT = "Поле '%s' должно быть числом.";
    const VALIDATION_INT_MIN = "Минимальная допустимая длина значения должна быть не меньше %d.";
    const VALIDATION_INT_MAX = "Максимальная допустимая длина значения должна быть не больше %d.";
    const VALIDATION_ARRAY = "Поле '%s' должно быть массивом.";
    const VALIDATION_ARRAY_MIN = "Минимальная допустимая длина массива должна быть не меньше %d элементов.";
    const VALIDATION_ARRAY_MAX = "Максимальная допустимая длина массива должна быть не больше %d элементов.";
    const VALIDATION_MONGO_ID = "Некорректный id.";

    public function validate(object $entity, array $rules): array
    {
        $errors = [];
        foreach ($rules as $property => $rule) {
            // Arrays of object.
            if (strpos($property, '.$.') !== false) {
                $parts = explode('.$.', $property);
                foreach ($entity->{$parts[0]} as $element) {
                    $err = $this->validateVar($element->{$parts[1]} ?? null, $rule);
                    if (!empty($err)) {
                        $errors[$property] = $err;
                    }
                }
                // Array.
            } elseif (strpos($property, '.$') !== false) {
                $parts = explode('.$', $property);
                foreach ($entity->{$parts[0]} as $element) {
                    $err = $this->validateVar($element, $rule);
                    if (!empty($err)) {
                        $errors[$property] = $err;
                    }
                }
                // Object.
            } elseif (strpos($property, '.') !== false) {
                $parts = explode('.', $property);
                $err = $this->validateVar($entity->{$parts[0]}->{$parts[1]} ?? null, $rule);
                if (!empty($err)) {
                    $errors[$property] = $err;
                }
            } else {
                // Plain value.
                $err = $this->validateVar($entity->{$property} ?? null, $rule);
                if (!empty($err)) {
                    $errors[$property] = $err;
                }
            }
        }

        return $errors;
    }

    /**
     * Validate array value.
     * @param array $value
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public function validateArray(array $value, int $min = 0, int $max = 0): ?string
    {
        $len = count($value);
        if ($min > 0 && $len < $min) {
            return sprintf(static::VALIDATION_ARRAY_MIN, $min);
        }
        if ($max > 0 && $len > $max) {
            return sprintf(static::VALIDATION_ARRAY_MAX, $max);
        }
        return null;
    }

    /**
     * Validate string with params.
     * @param string $value
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public function validateString(string $value, int $min = 0, int $max = 0): ?string
    {
        $len = strlen($value);
        if ($min > 0 && $len < $min) {
            return sprintf(static::VALIDATION_STRING_MIN, $min);
        }
        if ($max > 0 && $len > $max) {
            return sprintf(static::VALIDATION_STRING_MAX, $max);
        }
        return null;
    }

    /**
     * Valdate mongodb id.
     * @param string $id
     * @return string|null
     */
    public function validateMongoId(string $id): ?string
    {
        try {
            new ObjectId($id);
            return null;
        } catch (\Exception $e) {
            return static::VALIDATION_MONGO_ID;
        }
    }

    /**
     * Validate integer value.
     * @param int $value
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public function validateInt(int $value, int $min = 0, int $max = 0): ?string
    {
        if ($min > 0 && $value < $min) {
            return sprintf(static::VALIDATION_INT_MIN, $min);
        }
        if ($max > 0 && $value > $max) {
            return sprintf(static::VALIDATION_INT_MAX, $max);
        }
        return null;
    }

    private function validateVar($variable, array $rules): array
    {
        $errors = [];
        if ($variable === null) {
            if (in_array('required', $rules)) {
                $errors['required'] = static::VALIDATION_REQUIRED;
            }
            return $errors;
        }

        if (empty($variable)) {
            return [];
        }

        foreach ($rules as $ruleStr) {
            $ruleArr = explode(':', $ruleStr);
            $params = array_slice($ruleArr, 1);
            $method = 'validate' . ucfirst($ruleArr[0]);
            if (method_exists($this, $method)) {
                $error = call_user_func([$this, $method], $variable, $params);
                if (!empty($error)) {
                    $errors[$ruleArr[0]] = $error;
                }
            }
        }
        return $errors;
    }
}
