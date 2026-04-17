<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class H2hEndpoint extends Model
{
    protected $fillable = [
        'h2h_system_id',
        'name',
        'base_url',
        'path',
        'method',
        'timeout_seconds',
        'auth_profile_id',
        'default_headers',
    ];

    protected $casts = [
        'default_headers' => 'array',
    ];

    public function authProfile()
    {
        return $this->belongsTo(AuthProfile::class);
    }

    public function system()
    {
        return $this->belongsTo(H2hSystem::class, 'h2h_system_id');
    }

    public function testRuns()
    {
        return $this->hasMany(H2hTestRun::class);
    }
}
