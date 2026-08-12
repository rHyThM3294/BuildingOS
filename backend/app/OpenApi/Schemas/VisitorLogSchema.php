<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VisitorLog',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'visitorName', type: 'string'),
        new OA\Property(property: 'visitorType', type: 'string', enum: ['guest', 'delivery']),
        new OA\Property(property: 'targetUnit', type: 'string'),
        new OA\Property(property: 'status', type: 'string', enum: ['waiting', 'notified', 'entered', 'left']),
        new OA\Property(property: 'registeredAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'notifiedAt', type: 'string', format: 'date-time', nullable: true),
    ],
)]
class VisitorLogSchema
{
}
