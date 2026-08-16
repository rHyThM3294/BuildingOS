<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * 交通部 TDX 平臺的路外停車場即時空位。
 * OpenAPI 規格：https://tdx.transportdata.tw/api-service/swagger（停車資訊 v1）
 *
 * 兩支端點的回傳都是「包了一層的物件」，不是直接一個陣列：
 * { SrcUpdateTime, UpdateTime, AuthorityCode, Items: [...], Count }
 * 這點官方 Swagger UI 上的說明沒有講得很清楚，是直接讀 spec 的
 * schema 定義才確認的。AvailableSpaces = -1 代表「未知」，不是
 *「剩 0 位」，顯示時要特別處理，不然會誤導使用者。
 */
class TdxParkingService
{
    private const BASE_URL = 'https://tdx.transportdata.tw/api/basic';

    /** ParkingAvailability/City 端點實際有資料的縣市（CarPark 端點涵蓋更多縣市，但沒有即時空位）。 */
    public const AVAILABILITY_CITIES = [
        'Taipei', 'Taoyuan', 'Taichung', 'Tainan', 'Kaohsiung', 'Keelung',
        'ChanghuaCounty', 'YunlinCounty', 'PingtungCounty', 'YilanCounty',
        'HualienCounty', 'KinmenCounty',
    ];

    private const SERVICE_STATUS = [0 => '休息中', 1 => '營業中', 2 => '暫停營業', 3 => '非營業時段'];
    private const FULL_STATUS = [0 => '尚有空位', 1 => '車位將滿', 2 => '車位已滿', 3 => '過度擁擠'];

    public function __construct(private readonly TdxAuthService $auth)
    {
    }

    public function getAvailability(string $city): array
    {
        $token = $this->auth->getAccessToken();

        $availability = Http::withToken($token)
            ->get(self::BASE_URL."/v1/Parking/OffStreet/ParkingAvailability/City/{$city}", ['$format' => 'JSON']);

        $carParks = Http::withToken($token)
            ->get(self::BASE_URL."/v1/Parking/OffStreet/CarPark/City/{$city}", ['$format' => 'JSON']);

        $carParkById = collect($this->items($carParks->json()))->keyBy('CarParkID');

        return collect($this->items($availability->json()))
            ->map(function (array $item) use ($carParkById) {
                $carPark = $carParkById->get($item['CarParkID'] ?? null);
                $name = $item['CarParkName']['Zh_tw']
                    ?? $carPark['CarParkName']['Zh_tw']
                    ?? $item['CarParkName']['En']
                    ?? $item['CarParkID']
                    ?? '未命名停車場';

                $available = $item['AvailableSpaces'] ?? null;

                return [
                    'id' => $item['CarParkID'] ?? null,
                    'name' => $name,
                    'address' => $carPark['Address'] ?? null,
                    // -1 是 TDX 官方定義的「未知」，不是 0 個空位。
                    'availableSpaces' => ($available === null || $available < 0) ? null : (int) $available,
                    'totalSpaces' => isset($item['TotalSpaces']) ? (int) $item['TotalSpaces'] : null,
                    'serviceStatus' => self::SERVICE_STATUS[$item['ServiceStatus'] ?? null] ?? '未知',
                    'fullStatus' => self::FULL_STATUS[$item['FullStatus'] ?? null] ?? '未知',
                    'updatedAt' => $item['DataCollectTime'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * TDX 的回應是 { Items: [...] } 這種包裝過的物件；防禦性地處理，
     * 萬一哪天格式改成直接回傳陣列也不會整支噴掉。
     */
    private function items(mixed $payload): array
    {
        if (is_array($payload) && array_is_list($payload)) {
            return $payload;
        }

        return $payload['Items'] ?? [];
    }
}
