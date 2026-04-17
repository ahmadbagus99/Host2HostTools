<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class H2hRequestTemplate extends Model
{
    protected $fillable = [
        'h2h_system_id',
        'name',
        'description',
        'request_body',
    ];

    public function system()
    {
        return $this->belongsTo(H2hSystem::class, 'h2h_system_id');
    }
}
