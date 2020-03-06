<?php

declare(strict_types=1);

namespace App\Domain\Validation;

use Exception;
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
     * Validate an object against rules.
     * @param array $data
     * @param array $rules
     * @return array errors
     */
    public function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $property => $rule) {
            foreach ($rule as $r) {
                $err = $this->validateRule($property, $data, $r);
                if ($err !== []) {
                    $errors[$property] = $err;
                }
            }
        }
        return $errors;
    }

    public function validateRule(string $property, $data, string $rule): array
    {
        if (strpos($property, '.$.') !== false) {
            list($left, $right) = explode('.$.', $property, 2);
            if (!isset($data[$left])) {
                return [];
            }
            if (!is_array($data[$left])) {
                return ['Поле должно быть массивом.'];
            }
            $errors = [];
            foreach ($data[$left] as $i => $item) {
                $err = $this->validateRule($right, $item, $rule);
                if ($err !== []) {
                    $errors[$i] = $err;
                }
            }
            return $errors;
        } elseif (strpos($property, '.$') !== false) {
            list($left, $right) = explode('.$', $property, 2);
            if (!isset($data[$left])) {
                return [];
            }
            if (!is_array($data[$left])) {
                return ['Поле должно быть массивом.'];
            }
            $errors = [];
            foreach ($data[$left] as $i => $item) {
                $err = $this->validateRule((string)$i, $data[$left], $rule);
                if ($err !== []) {
                    $errors[$i] = $err;
                }
            }
            return $errors;
        } elseif (strpos($property, '.') !== false) {
            list($left, $right) = explode('.', $property, 2);
            return $this->validateRule($right, $data[$left], $rule);
        }

        return $this->validateValue($data[$property] ?? null, $rule);
    }

    public function validateValue($value, string $rule): array
    {
        $name = $rule;
        $params = [];
        if (strpos($rule, ':') !== false) {
            $params = explode(':', $rule);
            $name = $params[0];
        }
        $methodName = 'validate' . $this->toCamelCase($name);
        if (method_exists($this, $methodName)) {
            return call_user_func([$this, $methodName], $value, ...array_splice($params, 1));
        }
        return [];
    }

    public function validateRequired($value): array
    {
        $errors = [];
        if ($value === null) {
            $errors[] = 'Поле является обязательным.';
        }
        return $errors;
    }

    public function validateString($value, int $min = 0, int $max = 0): array
    {
        $errors = [];
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        if (!$value) {
            $errors[] = 'Поле должно быть строкой.';
        }
        $len = mb_strlen($value);
        if ($min !== 0 && $len < $min) {
            $errors[] = 'Минимальный размер строки должен быть больше чем ' . $min . ' символов.';
        }
        if ($max !== 0 && $len > $max) {
            $errors[] = 'Максимальный размер строки не должен превышать ' . $max . ' символов.';
        }
        return $errors;
    }

    public function validateInt($value, int $min = 0, int $max = 0): array
    {
        $errors = [];
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if (!$value) {
            $errors[] = 'Поле должно быть числом.';
        }
        if ($min !== 0 && $value < $min) {
            $errors[] = 'Минимальный размер значения должен быть больше чем ' . $min . '.';
        }
        if ($max !== 0 && $value > $max) {
            $errors[] = 'Максимальный размер значения не должен превышать ' . $max . '.';
        }
        return $errors;
    }

    public function validateBool($value): array
    {
        $errors = [];
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        if (!$value) {
            $errors[] = 'Значение должно быть булевым типом.';
        }
        return $errors;
    }

    public function validateEmail($value): array
    {
        $errors = [];
        $value = filter_var($value, FILTER_VALIDATE_EMAIL);
        if (!$value) {
            $errors[] = 'Значение должно быть правильным email адресом.';
        }
        return $errors;
    }

    public function validateArray($value, int $min = 0, int $max = 0): array
    {
        if ($value === null) {
            return [];
        }
        $errors = [];
        if (!is_array($value)) {
            $errors[] = 'Поле должно быть массивом.';
            return $errors;
        }
        $count = count($value);
        if ($min !== 0 && $count < $min) {
            $errors[] = 'Минимальный размер массива должен быть больше чем ' . $min . ' элементов.';
        }
        if ($max !== 0 && $count > $max) {
            $errors[] = 'Максимальный размер массива не должен превышать ' . $max . ' элементов.';
        }
        return $errors;
    }

    public function validateObjectId($value): array
    {
        $errors = [];
        try {
            new ObjectId($value);
        } catch (Exception $e) {
            $errors[] = 'Значение не является ObjectID.';
        }

        return $errors;
    }

    public function validateTime($value): array
    {
        if ($value === null) {
            return [];
        }
        $errors = [];
        if (!preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $value)) {
            $errors[] = 'Значение должно быть временем от 00:00 до 23:59.';
        }
        return $errors;
    }

    public function validateEnum($value, string $enums): array
    {
        $errors = [];
        $arr = explode(',', $enums);
        if (!in_array($value, $arr)) {
            $errors[] = 'Значение должно быть одним из: ' . $enums . '.';
        }
        return $errors;
    }

    private function toCamelCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
