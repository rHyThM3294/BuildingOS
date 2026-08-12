<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $fillable = [
        'visitor_name',
        'visitor_type',
        'target_unit',
        'status',
        'registered_at',
        'notified_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'notified_at' => 'datetime',
    ];
}
