<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VisitorLog',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'visitor_name', type: 'string'),
        new OA\Property(property: 'visitor_type', type: 'string', enum: ['guest', 'delivery']),
        new OA\Property(property: 'target_unit', type: 'string'),
        new OA\Property(property: 'status', type: 'string', enum: ['waiting', 'notified', 'entered', 'left']),
        new OA\Property(property: 'registered_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'notified_at', type: 'string', format: 'date-time', nullable: true),
    ],
)]
class VisitorLogSchema
{
}
