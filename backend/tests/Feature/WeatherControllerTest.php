<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * CWA 回傳的 JSON 內容是根據實際觀察到的真實回應（不是 Swagger UI 上不完整的
 * example）模擬的：Wx 用 parameterValue，PoP/MinT/MaxT 用 parameterName +
 * parameterUnit，CI 只有 parameterName；特報是 records.location[].hazardConditions
 * .hazards[]，時間在 hazard.validTime 底下（跟 info 同層，不是巢狀在裡面）。
 */
class WeatherControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.cwa.api_key' => 'test-key']);
    }

    public function test_forecast_returns_503_when_not_configured(): void
    {
        config(['services.cwa.api_key' => null]);

        $this->getJson('/api/weather/forecast')->assertStatus(503);
    }

    public function test_forecast_parses_real_cwa_response_shape(): void
    {
        Http::fake([
            '*/F-C0032-001*' => Http::response([
                'success' => 'true',
                'records' => [
                    'location' => [[
                        'locationName' => '臺北市',
                        'weatherElement' => [
                            ['elementName' => 'Wx', 'time' => [
                                ['startTime' => '2026-08-13 18:00:00', 'endTime' => '2026-08-14 06:00:00', 'parameter' => ['parameterName' => '多雲', 'parameterValue' => '4']],
                            ]],
                            ['elementName' => 'PoP', 'time' => [
                                ['startTime' => '2026-08-13 18:00:00', 'endTime' => '2026-08-14 06:00:00', 'parameter' => ['parameterName' => '20', 'parameterUnit' => '百分比']],
                            ]],
                            ['elementName' => 'MinT', 'time' => [
                                ['parameter' => ['parameterName' => '26', 'parameterUnit' => 'C']],
                            ]],
                            ['elementName' => 'CI', 'time' => [
                                ['parameter' => ['parameterName' => '舒適']],
                            ]],
                            ['elementName' => 'MaxT', 'time' => [
                                ['parameter' => ['parameterName' => '32', 'parameterUnit' => 'C']],
                            ]],
                        ],
                    ]],
                ],
            ], 200),
        ]);

        $this->getJson('/api/weather/forecast?city=臺北市')
            ->assertOk()
            ->assertJson([
                'city' => '臺北市',
                'description' => '多雲',
                'minTemp' => 26,
                'maxTemp' => 32,
                'pop' => 20,
                'comfort' => '舒適',
            ]);
    }

    public function test_alerts_parses_real_cwa_response_shape(): void
    {
        Http::fake([
            '*/W-C0033-001*' => Http::response([
                'success' => 'true',
                'records' => [
                    'location' => [
                        [
                            'locationName' => '臺北市',
                            'hazardConditions' => ['hazards' => [
                                [
                                    'info' => ['language' => 'zh-TW', 'phenomena' => '大雨', 'significance' => '特報'],
                                    'validTime' => ['startTime' => '2026-08-13 14:00:00', 'endTime' => '2026-08-14 08:00:00'],
                                ],
                            ]],
                        ],
                        [
                            'locationName' => '新竹縣',
                            'hazardConditions' => ['hazards' => []],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->getJson('/api/weather/alerts?city=臺北市')
            ->assertOk()
            ->assertJson([[
                'phenomena' => '大雨',
                'significance' => '特報',
                'startTime' => '2026-08-13 14:00:00',
                'endTime' => '2026-08-14 08:00:00',
            ]]);

        $this->getJson('/api/weather/alerts?city=新竹縣')
            ->assertOk()
            ->assertExactJson([]);
    }
}
