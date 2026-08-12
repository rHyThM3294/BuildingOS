<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ParkingLog',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'plate_number', type: 'string'),
        new OA\Property(property: 'direction', type: 'string', enum: ['in', 'out']),
        new OA\Property(property: 'status', type: 'string', enum: ['success', 'failed']),
        new OA\Property(property: 'owner_name', type: 'string', nullable: true),
        new OA\Property(property: 'recognized_at', type: 'string', format: 'date-time'),
    ],
)]
class ParkingLogSchema
{
}
