<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'client_id',
        'client_secret',
        'sbx_client_id',
        'sbx_client_secret',
        'whitelist_ip',
        'default_gateway',
        'callback_secret',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    public function token()
    {
        return $this->hasMany(Token::class);
    }
}
