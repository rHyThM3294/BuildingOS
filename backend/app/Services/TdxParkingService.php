<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * 交通部 TDX 平臺的路外停車場即時空位。
 * OpenAPI 規格：https://tdx.transportdata.tw/api-service/swagger（停車資訊 v1）
 *
 * 兩支端點的回傳都是「包了一層的物件」，不是直接一個陣列，但陣列的
 * key 是各自資源專屬的名稱，不是 spec schema 裡看起來共用的 Items：
 * ParkingAvailability 端點是 `ParkingAvailabilities`，CarPark 端點
 * 是 `CarParks`。這是拿到真實金鑰、實際呼叫過後才發現跟 OpenAPI
 * spec 的 schema 定義兜不起來的地方——spec 只保證欄位「長什麼樣」，
 * 不保證跟實際 serialize 出來的 key 一致，串真的 API 之前最好都
 * 實際打一次確認。AvailableSpaces = -1 代表「未知」，不是「剩 0
 * 位」，顯示時要特別處理，不然會誤導使用者。
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

    /**
     * 台北市單一縣市就有超過 1000 筆停車場資料，這支 API 沒有座標可以
     * 做「附近」的地理篩選，只能先用 $limit 控制回傳筆數，不然畫面會
     * 被撐成一張幾萬像素高的表格。用 $top 直接請 TDX 只回傳需要的筆
     * 數，同時也少一點資料要傳輸；合併完之後再依「有剩餘車位的排前
     * 面、剩餘車位多的排前面」排序，示範起來比隨機切一段有意義。
     */
    public function getAvailability(string $city, int $limit = 20): array
    {
        $token = $this->auth->getAccessToken();

        $availability = Http::withToken($token)
            ->get(self::BASE_URL."/v1/Parking/OffStreet/ParkingAvailability/City/{$city}", [
                '$format' => 'JSON',
                '$top' => $limit,
            ]);

        $carParks = Http::withToken($token)
            ->get(self::BASE_URL."/v1/Parking/OffStreet/CarPark/City/{$city}", ['$format' => 'JSON']);

        $carParkById = collect($this->items($carParks->json(), 'CarParks'))->keyBy('CarParkID');

        return collect($this->items($availability->json(), 'ParkingAvailabilities'))
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
            ->sortByDesc(fn ($lot) => $lot['availableSpaces'] ?? -1)
            ->values()
            ->all();
    }

    /**
     * TDX 的回應是包裝過的物件，實際陣列的 key 因端點而異（不是統一
     * 叫 Items）。$primaryKey 放實測確認過的正確 key，另外保留幾個
     * 備援 key 名稱，避免哪天格式微調就整支噴掉。
     */
    private function items(mixed $payload, string $primaryKey): array
    {
        if (is_array($payload) && array_is_list($payload)) {
            return $payload;
        }

        foreach ([$primaryKey, 'Items'] as $key) {
            if (isset($payload[$key])) {
                return $payload[$key];
            }
        }

        return [];
    }
}
