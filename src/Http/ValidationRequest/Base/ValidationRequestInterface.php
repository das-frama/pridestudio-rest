<?php
declare(strict_types=1);

namespace App\Http\ValidationRequest\Base;

interface ValidationRequestInterface
{
    /**
     * @return array
     */
    public function rules(): array;
}