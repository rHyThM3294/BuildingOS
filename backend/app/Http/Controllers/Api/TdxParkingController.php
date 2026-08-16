<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TdxNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Services\TdxParkingService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TdxParkingController extends Controller
{
    public function __construct(private readonly TdxParkingService $tdx)
    {
    }

    #[OA\Get(
        path: '/parking/nearby-availability',
        operationId: 'listNearbyParkingAvailability',
        summary: '查詢附近公共停車場即時空位（轉發交通部 TDX 開放資料，OAuth2 client_credentials）',
        tags: ['Parking'],
        parameters: [
            new OA\Parameter(name: 'city', in: 'query', description: '縣市（英文代碼，例如 Taipei），預設值可於後端設定', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: '成功', content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/ParkingLotAvailability'),
            )),
            new OA\Response(response: 422, description: '不支援的縣市代碼'),
            new OA\Response(response: 503, description: '後端尚未設定 TDX_CLIENT_ID / TDX_CLIENT_SECRET'),
        ],
    )]
    public function index(Request $request)
    {
        $city = $request->query('city', config('services.tdx.default_city'));

        abort_unless(
            in_array($city, TdxParkingService::AVAILABILITY_CITIES, true),
            422,
            "不支援的縣市代碼「{$city}」，可用值：".implode('、', TdxParkingService::AVAILABILITY_CITIES),
        );

        try {
            $lots = $this->tdx->getAvailability($city);
        } catch (TdxNotConfiguredException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json($lots);
    }
}
