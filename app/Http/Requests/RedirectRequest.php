<?php

namespace App\Http\Requests;

use App\Rules\ValidateUrlRule;
use App\Rules\NotSameUrlRule;
use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'url' => [
                'required',
                'url:https',
                'starts_with:https',
                new ValidateUrlRule(),
                new NotSameUrlRule()
            ]
        ];
    }
}

