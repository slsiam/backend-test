<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ValidateUrlRule implements Rule
{
    public function passes($attribute, $value)
    {
        try {
            $response = Http::get($value);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message()
    {
        return 'A URL de destino não está ativa.';
    }

}
