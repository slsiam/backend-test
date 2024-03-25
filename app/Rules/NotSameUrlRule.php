<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotSameUrlRule implements Rule
{
    public function passes($attribute, $value)
    {
        return parse_url($value, PHP_URL_HOST) !== request()->getHttpHost();
    }

    public function message()
    {
        return 'A URL de destino não pode apontar para a própria aplicação.';
    }
}
