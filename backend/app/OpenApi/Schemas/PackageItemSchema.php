<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PackageItem',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'trackingNo', type: 'string'),
        new OA\Property(property: 'recipientUnit', type: 'string'),
        new OA\Property(property: 'recipientName', type: 'string'),
        new OA\Property(property: 'courier', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'notified', 'collected']),
        new OA\Property(property: 'arrivedAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'collectedAt', type: 'string', format: 'date-time', nullable: true),
    ],
)]
class PackageItemSchema
{
}
