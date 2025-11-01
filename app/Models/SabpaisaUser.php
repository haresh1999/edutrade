<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SabpaisaUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'client_id',
        'client_secret',
        'sandbox_client_id',
        'sandbox_client_secret',
        'callback_url',
        'redirect_url',
        'whitelist_ip',
    ];
}
