<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthProfile extends Model
{
    protected $fillable = [
        'h2h_system_id',
        'name',
        'auth_type',
        'token',
        'username',
        'password',
        'creatio_login_path',
        'api_key',
        'api_key_header',
        'extra_headers',
    ];

    protected $casts = [
        'extra_headers' => 'array',
    ];

    public function endpoints()
    {
        return $this->hasMany(H2hEndpoint::class);
    }

    public function system()
    {
        return $this->belongsTo(H2hSystem::class, 'h2h_system_id');
    }
}
