<?php

namespace App\Http\Requests;

use App\Http\Requests\RedirectRequest;

class RedirectUpdateRequest extends RedirectRequest
{
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                'active' => ['required', 'boolean']
            ]
        );
    }

}
