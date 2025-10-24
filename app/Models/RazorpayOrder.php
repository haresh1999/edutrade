<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RazorpayOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_id',
        'tnx_id',
        'amount',
        'status',
        'payer_name',
        'payer_email',
        'payer_mobile',
        'request_response',
        'refund_amount',
        'refund_response',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->tnx_id = str()->uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(RazorpayUser::class, 'user_id');
    }
}
