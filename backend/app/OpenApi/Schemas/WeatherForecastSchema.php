<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WeatherForecast',
    required: ['city', 'description', 'minTemp', 'maxTemp', 'pop', 'comfort', 'startTime', 'endTime'],
    properties: [
        new OA\Property(property: 'city', type: 'string', example: '臺北市'),
        new OA\Property(property: 'description', type: 'string', example: '多雲'),
        new OA\Property(property: 'minTemp', type: 'integer', example: 24),
        new OA\Property(property: 'maxTemp', type: 'integer', example: 31),
        new OA\Property(property: 'pop', type: 'integer', description: '降雨機率 (%)', example: 20),
        new OA\Property(property: 'comfort', type: 'string', example: '悶熱'),
        new OA\Property(property: 'startTime', type: 'string', format: 'date-time'),
        new OA\Property(property: 'endTime', type: 'string', format: 'date-time'),
    ],
)]
class WeatherForecastSchema
{
}
