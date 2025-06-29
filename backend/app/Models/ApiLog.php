<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $table = 'api_logs';
    public $timestamps = false;
    protected $fillable = [
        'method',
        'endpoint',
        'timestamp',
        'duration_ms',
        'status_code',
        'user_agent',
        'ip',
    ];
}
