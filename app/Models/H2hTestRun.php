<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class H2hTestRun extends Model
{
    protected $fillable = [
        'h2h_system_id',
        'h2h_endpoint_id',
        'request_url',
        'request_method',
        'request_headers',
        'request_body',
        'response_status',
        'response_headers',
        'response_body',
        'duration_ms',
        'error_message',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
    ];

    public function endpoint()
    {
        return $this->belongsTo(H2hEndpoint::class, 'h2h_endpoint_id');
    }

    public function system()
    {
        return $this->belongsTo(H2hSystem::class, 'h2h_system_id');
    }
}
