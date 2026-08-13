<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WeatherAlert',
    required: ['phenomena', 'significance', 'startTime', 'endTime'],
    properties: [
        new OA\Property(property: 'phenomena', type: 'string', example: '大雨'),
        new OA\Property(property: 'significance', type: 'string', example: '特報'),
        new OA\Property(property: 'startTime', type: 'string', format: 'date-time'),
        new OA\Property(property: 'endTime', type: 'string', format: 'date-time'),
    ],
)]
class WeatherAlertSchema
{
}
