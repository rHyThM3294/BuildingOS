<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageItem extends Model
{
    protected $fillable = [
        'tracking_no',
        'recipient_unit',
        'recipient_name',
        'courier',
        'status',
        'arrived_at',
        'collected_at',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'collected_at' => 'datetime',
    ];
}
