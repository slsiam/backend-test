<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Redirect extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['url', 'active'];

    protected $hidden = ['id'];

    protected $appends = ['code'];

    public function getCodeAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $id = Hashids::decode($value);
        return $this->where('id', $id)->first();
    }

    public function redirectLogs(): HasMany
    {
        return $this->hasMany(RedirectLog::class);
    }
    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->active = false;
            $model->save();
        });
    }

}
