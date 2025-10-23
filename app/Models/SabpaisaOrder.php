<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SabpaisaOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'status',
        'payer_name',
        'payer_email',
        'payer_mobile',
        'request_response',
        'refund_amount',
        'refund_response',
    ];

    public function user()
    {
        return $this->belongsTo(SabpaisaUser::class, 'user_id');
    }
}
