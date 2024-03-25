<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoEmptyQueryParams implements Rule
{
    public function passes($attribute, $value)
    {
        $query = parse_url($value, PHP_URL_QUERY);

        if (!$query) {
            return true;
        }
        parse_str($query, $queryParams);

        return !in_array('', $queryParams, true);
    }

    public function message()
    {
        return 'A URL não pode conter parâmetros de consulta com valores vazios.';
    }
}
