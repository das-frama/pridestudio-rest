<?php

declare(strict_types=1);

namespace app\domain\validation;

use Mongodb\BSON\ObjectId;

class ValidationService
{
    const VALIDATION_REQUIRED = "Поле обязательно к заполению.";
    const VALIDATION_ENUM = "Значение '%s' не присутствует в списке допустимых значений.";
    const VALIDATION_EMAIL = "Значение '%s' должно быть правильным email адресом.";
    const VALIDATION_STRING = "Поле '%s' должно быть строкой.";
    const VALIDATION_STRING_MIN = "Минимальная допустимая длина строки должна быть не меньше %d символов.";
    const VALIDATION_STRING_MAX = "Максимальная допустимая длина строки должна быть не больше %d символов.";
    const VALIDATION_BOOL = "Значение '%s' не является булевым типом.";
    const VALIDATION_INT = "Значение '%s' не является числом.";
    const VALIDATION_INT_MIN = "Минимальная допустимая длина значения должна быть не меньше %d.";
    const VALIDATION_INT_MAX = "Максимальная допустимая длина значения должна быть не больше %d.";
    const VALIDATION_ARRAY = "Значение не является массивом.";
    const VALIDATION_ARRAY_MIN = "Минимальная допустимая длина массива должна быть не меньше %d элементов.";
    const VALIDATION_ARRAY_MAX = "Максимальная допустимая длина массива должна быть не больше %d элементов.";
    const VALIDATION_MONGO_ID = "Некорректный id.";

    /**
     * Sanitize data.
     * @param object $data
     * @param array $rules
     * @return object
     */
    public function sanitize(object $data, array $rules): object
    {
        $result = [];
        foreach ($rules as $property => $rule) {
            if (strpos($property, '.$.') !== false) {
                // Array of objects.
                list($left, $right) = explode('.$.', $property, 2);
                if (!isset($result[$left])) {
                    $result[$left] = [];
                }
                if (!isset($data->{$left}) || !is_array($data->{$left})) {
                    continue;
                }
                foreach ($data->{$left} as $i => $element) {
                    $result[$left][$i][$right] = $this->sanitizeValue($element->{$right} ?? null, $rule);
                }
            } elseif (strpos($property, '.$') !== false) {
                // Array of scalars.
                $left = strstr($property, '.$', true);
                if (!isset($result[$left])) {
                    $result[$left] = [];
                }
                if (!isset($data->{$left}) || !is_array($data->{$left})) {
                    continue;
                }
                foreach ($data->{$left} as $i => $element) {
                    $result[$left][$i] = $this->sanitizeValue($element, $rule);
                }
            } elseif (strpos($property, '.') !== false) {
                // Object.
                list($left, $right) = explode('.', $property, 2);
                $result[$left][$right] = $this->sanitizeValue($data->{$left}->{$right} ?? null, $rule);
            } else {
                // Plain value.
                $result[$property] = $this->sanitizeValue($data->{$property} ?? null, $rule);
            }
        }
        return json_decode(json_encode($result));
    }

    /**
     * Get sanitized value.
     * @param mixed $value
     * @param array $rules
     * @return mixed
     */
    public function sanitizeValue($value, array $rules)
    {
        // if ($value === null) {
        //     return null;
        // }
        // if (is_object($value)) {
        //     $value = (array) $value;
        // }
        foreach ($rules as $rule) {
            if ($rule === 'required') {
                continue;
            }
            if (strpos($rule, ':') !== false) {
                $ruleName = strstr($rule, ':', true);
            } else {
                $ruleName = $rule;
            }
            switch ($ruleName) {
                case 'string':
                    return filter_var($value, FILTER_SANITIZE_STRING);
                case 'email':
                    return filter_var($value, FILTER_SANITIZE_EMAIL);
                case 'int':
                    return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                case 'url':
                    return filter_var($value, FILTER_SANITIZE_URL);
                case 'array':
                    return [];
            }
        }
        return $value;
    }

    /**
     * Validate an object against rules.
     * @param object $data
     * @param array $rules
     * @return array
     */
    public function validate(object $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $property => $rule) {
            if (strpos($property, '.$.') !== false) {
                // Array of objects.
                list($left, $right) = explode('.$.', $property, 2);
                if (!isset($data->{$left}) || !is_array($data->{$left})) {
                    continue;
                }
                foreach ($data->{$left} as $i => &$element) {
                    $err = $this->validateValue($element->{$right} ?? null, $rule);
                    if ($err !== null) {
                        $errors[$right] = $err;
                    }
                }
            } elseif (strpos($property, '.$') !== false) {
                // Array of scalars.
                $left = strstr($property, '.$', true);
                if (!isset($data->{$left}) || !is_array($data->{$left})) {
                    continue;
                }
                foreach ($data->{$left} as $i => &$element) {
                    $err = $this->validateValue($element, $rule);
                    if ($err !== null) {
                        $errors[$left] = $err;
                    }
                }
            } elseif (strpos($property, '.') !== false) {
                list($left, $right) = explode('.', $property, 2);
                if (!isset($data->{$left}->{$right})) {
                    continue;
                }
                $err = $this->validateValue($data->{$left}->{$right}, $rule);
                if ($err !== null) {
                    $errors[$right] = $err;
                }
            } else {
                $err = $this->validateValue($data->{$property} ?? null, $rule);
                if ($err !== null) {
                    $errors[$property] = $err;
                }
            }
        }
        return $errors;
    }

    /**
     * Validate an object against single rule.
     * @param mixed $value
     * @param string $property
     * @param array $rules
     * @return string|null
     */
    public function validateValue($value, array $rules): ?string
    {
        if (empty($value)) {
            if (in_array('required', $rules)) {
                return static::VALIDATION_REQUIRED;
            }
            return null;
        }

        foreach ($rules as $rule) {
            if (strpos($rule, ':') !== false) {
                $params = explode(':', $rule);
                $ruleName = $params[0];
                if (count($params) > 1) {
                    $params = array_slice($params, 1);
                }
            } else {
                $ruleName = $rule;
                $params = [];
            }
            $method = 'validate' . ucfirst($ruleName);
            if (method_exists($this, $method)) {
                $err = call_user_func([$this, $method], $value, ...$params);
                if ($err !== null) {
                    return $err;
                }
            }
        }
        return null;
    }

    /**
     * Validate array value.
     * @param mixed $value
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public function validateArray($value, int $min = 0, int $max = 0): ?string
    {
        if (!is_array($value)) {
            return static::VALIDATION_ARRAY;
        }
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
     * @param mixed $value
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public function validateString($value, int $min = 0, int $max = 0): ?string
    {
        if (!is_string($value)) {
            return sprintf(static::VALIDATION_STRING, $value);
        }
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
     * Validate bool with params.
     * @param mixed $value
     * @return string|null
     */
    public function validateBool($value): ?string
    {
        if (!filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return sprintf(static::VALIDATION_BOOL, $value);
        }
        return null;
    }

    /**
     * Validate email with params.
     * @param string $value
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public function validateEmail(string $value, int $min = 0, int $max = 0): ?string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return sprintf(static::VALIDATION_EMAIL, $value);
        }
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
     * Validate enum with params.
     * @param string $value
     * @param string $values
     * @return string|null
     */
    public function validateEnum(string $value, string $values): ?string
    {
        $params = explode(',', $values);
        if (!in_array($value, $params)) {
            return sprintf(static::VALIDATION_ENUM, $value);
        }
        return null;
    }

    /**
     * Valdate mongodb id.
     * @param mixed $id
     * @return string|null
     */
    public function validateMongoid($id): ?string
    {
        try {
            new ObjectId((string) $id);
            return null;
        } catch (\Exception $e) {
            return static::VALIDATION_MONGO_ID;
        }
    }

    /**
     * Validate integer value.
     * @param mixed $value
     * @param int $min
     * @param int $max
     * @return string|null
     */
    public function validateInt($value, int $min = 0, int $max = 0): ?string
    {
        if (!is_int($value)) {
            return sprintf(static::VALIDATION_INT, 'test');
        }
        if ($min > 0 && $value < $min) {
            return sprintf(static::VALIDATION_INT_MIN, $min);
        }
        if ($max > 0 && $value > $max) {
            return sprintf(static::VALIDATION_INT_MAX, $max);
        }
        return null;
    }
}
