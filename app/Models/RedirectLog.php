<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectLog extends Model
{
    protected $fillable = ['redirect_id', 'ip_address', 'user_agent', 'referer', 'query_params', 'created_at'];

    protected $hidden = ['id', 'redirect_id'];

    public function redirect()
    {
        return $this->belongsTo(Redirect::class);
    }
}