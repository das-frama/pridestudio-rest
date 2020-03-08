<?php
declare(strict_types=1);

namespace App\Http\ValidationRequests\Base;

interface ValidationRequestInterface
{
    /**
     * @return array
     */
    public function rules(): array;
}