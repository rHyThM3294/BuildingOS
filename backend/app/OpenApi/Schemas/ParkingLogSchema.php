<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ParkingLog',
    required: ['id', 'plateNumber', 'direction', 'status', 'ownerName', 'recognizedAt'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'plateNumber', type: 'string'),
        new OA\Property(property: 'direction', type: 'string', enum: ['in', 'out']),
        new OA\Property(property: 'status', type: 'string', enum: ['success', 'failed']),
        new OA\Property(property: 'ownerName', type: 'string', nullable: true),
        new OA\Property(property: 'recognizedAt', type: 'string', format: 'date-time'),
    ],
)]
class ParkingLogSchema
{
}
