<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PackageItem',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'tracking_no', type: 'string'),
        new OA\Property(property: 'recipient_unit', type: 'string'),
        new OA\Property(property: 'recipient_name', type: 'string'),
        new OA\Property(property: 'courier', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'notified', 'collected']),
        new OA\Property(property: 'arrived_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'collected_at', type: 'string', format: 'date-time', nullable: true),
    ],
)]
class PackageItemSchema
{
}
