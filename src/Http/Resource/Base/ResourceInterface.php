<?php

namespace App\Http\Resource\Base;

interface ResourceInterface
{
    /**
     * @return array
     */
    public function rules(): array;
}