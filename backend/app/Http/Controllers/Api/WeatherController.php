<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CwaNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Services\CwaWeatherService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class WeatherController extends Controller
{
    public function __construct(private readonly CwaWeatherService $cwa)
    {
    }

    #[OA\Get(
        path: '/weather/forecast',
        operationId: 'getWeatherForecast',
        summary: '取得指定縣市的 36 小時天氣預報（轉發中央氣象署 CWA 開放資料）',
        tags: ['Weather'],
        parameters: [
            new OA\Parameter(name: 'city', in: 'query', description: '縣市名稱，例如「臺北市」，預設值可於後端設定', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: '成功', content: new OA\JsonContent(ref: '#/components/schemas/WeatherForecast')),
            new OA\Response(response: 404, description: '查無此縣市的預報資料'),
            new OA\Response(response: 503, description: '後端尚未設定 CWA_API_KEY'),
        ],
    )]
    public function forecast(Request $request)
    {
        $city = $request->query('city', config('services.cwa.default_city'));

        try {
            $forecast = $this->cwa->getForecast($city);
        } catch (CwaNotConfiguredException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        abort_if($forecast === null, 404, "查無「{$city}」的天氣預報資料");

        return response()->json($forecast);
    }

    #[OA\Get(
        path: '/weather/alerts',
        operationId: 'listWeatherAlerts',
        summary: '取得指定縣市目前生效中的天氣特報（轉發中央氣象署 CWA 開放資料）',
        tags: ['Weather'],
        parameters: [
            new OA\Parameter(name: 'city', in: 'query', description: '縣市名稱，例如「臺北市」，預設值可於後端設定', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: '成功（沒有特報時回傳空陣列）', content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/WeatherAlert'),
            )),
            new OA\Response(response: 503, description: '後端尚未設定 CWA_API_KEY'),
        ],
    )]
    public function alerts(Request $request)
    {
        $city = $request->query('city', config('services.cwa.default_city'));

        try {
            $alerts = $this->cwa->getAlerts($city);
        } catch (CwaNotConfiguredException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json($alerts);
    }
}
