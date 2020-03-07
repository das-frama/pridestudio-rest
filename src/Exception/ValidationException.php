<?php
declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Class ValidationException
 * @package App\Exception
 */
class ValidationException extends RuntimeException
{
    private array $errors = [];

    /**
     * ValidationException constructor.
     * @param string $message
     * @param array $errors
     * @param int $code
     * @param RuntimeException|null $previous
     */
    public function __construct(string $message, array $errors = [], int $code = 0, RuntimeException $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}