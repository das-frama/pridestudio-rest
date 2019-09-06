<?php

declare(strict_types=1);

namespace app\domain\validation;

class IntValidator implements ValidatorInterface
{
    public $min;
    public $max;

    public function __construct(array $rules)
    {
        $count = count($rules);
        if ($count == 1) {
            $this->max = $rules[0];
        } elseif ($count == 2) {
            $this->min = $rules[0];
            $this->max = $rules[1];
        }
    }
}
