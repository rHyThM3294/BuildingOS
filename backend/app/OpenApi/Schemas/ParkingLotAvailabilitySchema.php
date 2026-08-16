<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ParkingLotAvailability',
    required: ['id', 'name', 'address', 'availableSpaces', 'totalSpaces', 'serviceStatus', 'fullStatus', 'updatedAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'TPE_C001'),
        new OA\Property(property: 'name', type: 'string', example: '市民廣場地下停車場'),
        new OA\Property(property: 'address', type: 'string', nullable: true),
        new OA\Property(property: 'availableSpaces', type: 'integer', nullable: true, description: '目前剩餘車位；null 表示現場未回報（TDX 原始值為 -1）'),
        new OA\Property(property: 'totalSpaces', type: 'integer', nullable: true),
        new OA\Property(property: 'serviceStatus', type: 'string', example: '營業中'),
        new OA\Property(property: 'fullStatus', type: 'string', example: '尚有空位'),
        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time', nullable: true),
    ],
)]
class ParkingLotAvailabilitySchema
{
}
