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

    /**
     * Validate an object against rules.
     * @param object $entity
     * @param array $rules
     * @return array
     */
    public function validate(object $entity, array $rules): array
    {
        $errors = [];
        foreach ($rules as $property => $rule) {
            $err = $this->validateRule($entity, $property, $rule);
            if ($err !== null) {
                $errors[] = $err;
            }
        }
        return $errors;
    }

    /**
     * Validate an object against single rule.
     * @param object $entity
     * @param string $property
     * @param array $rules
     * @return string|null
     */
    public function validateRule(object $entity, string $property, array $rules): ?string
    {
        // Array of objects.
        if (strpos($property, '.$.') !== false) {
            list($left, $right) = explode('.$.', $property, 2);
            if (!isset($entity->{$left}) || !is_array($entity->{$left})) {
                return null;
            }
            foreach ($entity->{$left} as $e) {
                $err = $this->validateType($e->{$right} ?? null, $property, $rules);
                if ($err !== null) {
                    return $err;
                }
            }
        } elseif (strpos($property, '.$') !== false) {
            // Array.
            $left = strstr($property, '.$', true);
            if (!isset($entity->{$left})) {
                return in_array('required', $rules) ? sprintf(static::VALIDATION_REQUIRED, $property) : null;
            }
            if (!is_array($entity->{$left})) {
                return null;
            }
            foreach ($entity->{$left} as $item) {
                $err = $this->validateType($item, $property, $rules);
                if ($err !== null) {
                    return $err;
                }
            }
        } elseif (strpos($property, '.') !== false) {
            // Object.
            list($left, $right) = explode('.', $property, 2);
            $err = $this->validateType($entity->{$left} ?? null, $property, $rules);
            if ($err !== null) {
                return $err;
            }
        } else {
            // Plain value.
            $err = $this->validateType($entity->{$property} ?? null, $property, $rules);
            if ($err !== null) {
                return $err;
            }
        }

        return null;
    }

    /**
     * Validate type of property.
     * @param mixed $value
     * @param array $rules
     * @return string|null
     */
    public function validateType($value, string $property, array $rules): ?string
    {
        if ($value === null) {
            return in_array('required', $rules) ? sprintf(static::VALIDATION_REQUIRED, $property) : null;
        }
        foreach ($rules as $rule) {
            if (strpos($rule, ':') !== false) {
                list($ruleName, $min, $max) = explode(':', $rule, 3);
                if (gettype($value) !== $ruleName) {
                    $const = 'VALIDATION_' . strtoupper($value);
                    return sprintf(static::$const, $property);
                }
            } else {
                $ruleName = $rule;
                $min = $max = 0;
            }
            $method = 'validate' . ucfirst($ruleName);
            if (method_exists($this, $method)) {
                return call_user_func([$this, $method], $value, $min, $max);
            }
        }
        return null;
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
    public function validateMongoid(string $id): ?string
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
}
