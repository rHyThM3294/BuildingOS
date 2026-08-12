<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingLog extends Model
{
    protected $fillable = [
        'plate_number',
        'direction',
        'status',
        'owner_name',
        'recognized_at',
    ];

    protected $casts = [
        'recognized_at' => 'datetime',
    ];
}
