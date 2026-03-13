<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Token extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'token',
        'ip_address',
        'env'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->env = config('services.env');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
