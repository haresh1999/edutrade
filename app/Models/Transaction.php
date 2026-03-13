<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_id',
        'payer_name',
        'payer_email',
        'payer_mobile',
        'status',
        'gateway',
        'env',
        'amount',
        'payment_response',
        'refund_amount',
        'refund_response',
        'redirect_url',
        'callback_url',
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
