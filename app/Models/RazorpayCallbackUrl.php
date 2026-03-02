<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RazorpayCallbackUrl extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_id',
        'tnx_id',
        'redirect_url',
        'callback_url',
    ];
}
