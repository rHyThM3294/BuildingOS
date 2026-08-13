<?php

namespace App\Services;

use App\Exceptions\CwaNotConfiguredException;
use Illuminate\Support\Facades\Http;

/**
 * 中央氣象署 (CWA) 開放資料平臺轉發。
 * Swagger/OpenAPI 文件：https://opendata.cwa.gov.tw/dist/opendata-swagger.html
 * 免費會員金鑰申請：https://opendata.cwa.gov.tw/user/authkey
 *
 * 回傳格式是實際呼叫過 API 才確認下來的，官方 Swagger UI 上的 example
 * 並不完整：Wx 用 parameterValue（天氣代碼），PoP/MinT/MaxT 用
 * parameterName + parameterUnit，CI 只有 parameterName，沒有 unit。
 */
class CwaWeatherService
{
    private const BASE_URL = 'https://opendata.cwa.gov.tw/api/v1/rest/datastore';

    private function apiKey(): string
    {
        $key = config('services.cwa.api_key');

        if (! $key) {
            throw new CwaNotConfiguredException();
        }

        return $key;
    }

    /**
     * 36 小時天氣預報（F-C0032-001），取最近一個預報時段。
     */
    public function getForecast(string $city): ?array
    {
        $response = Http::get(self::BASE_URL.'/F-C0032-001', [
            'Authorization' => $this->apiKey(),
            'locationName' => $city,
        ]);

        $location = $response->json('records.location.0');
        if (! $location) {
            return null;
        }

        $elements = collect($location['weatherElement'] ?? [])
            ->keyBy('elementName');

        $valueOf = fn (string $element) => $elements->get($element)['time'][0]['parameter']['parameterName'] ?? null;

        return [
            'city' => $location['locationName'],
            'description' => $valueOf('Wx'),
            'minTemp' => (int) $valueOf('MinT'),
            'maxTemp' => (int) $valueOf('MaxT'),
            'pop' => (int) $valueOf('PoP'),
            'comfort' => $valueOf('CI'),
            'startTime' => $elements->get('Wx')['time'][0]['startTime'] ?? null,
            'endTime' => $elements->get('Wx')['time'][0]['endTime'] ?? null,
        ];
    }

    /**
     * 天氣特報（W-C0033-001），回傳指定縣市目前生效中的特報清單（可能是空陣列）。
     */
    public function getAlerts(string $city): array
    {
        $response = Http::get(self::BASE_URL.'/W-C0033-001', [
            'Authorization' => $this->apiKey(),
            'locationName' => $city,
        ]);

        $location = collect($response->json('records.location', []))
            ->firstWhere('locationName', $city);

        $hazards = $location['hazardConditions']['hazards'] ?? [];

        return collect($hazards)->map(fn ($hazard) => [
            'phenomena' => $hazard['info']['phenomena'] ?? null,
            'significance' => $hazard['info']['significance'] ?? null,
            'startTime' => $hazard['validTime']['startTime'] ?? null,
            'endTime' => $hazard['validTime']['endTime'] ?? null,
        ])->values()->all();
    }
}
