<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazorpaySandboxToken extends Model
{
    protected $fillable = [
        'user_id',
        'token'
    ];
}
