<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class H2hSystem extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function authProfiles()
    {
        return $this->hasMany(AuthProfile::class);
    }

    public function endpoints()
    {
        return $this->hasMany(H2hEndpoint::class);
    }

    public function testRuns()
    {
        return $this->hasMany(H2hTestRun::class);
    }

    public function requestTemplates()
    {
        return $this->hasMany(H2hRequestTemplate::class);
    }
}
