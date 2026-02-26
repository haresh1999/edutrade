<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazorpayToken extends Model
{
    protected $fillable = [
        'user_id',
        'token'
    ];
}
