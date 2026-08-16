<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * TDX 回應是包了一層的物件（{Items: [...]}），不是直接一個陣列，
 * AvailableSpaces = -1 代表「未知」。這兩點是直接讀 TDX 官方 OpenAPI
 * spec 的 schema 定義確認的，不是憑印象猜的。
 */
class TdxParkingControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.tdx.client_id' => 'test-id', 'services.tdx.client_secret' => 'test-secret']);
        Cache::forget('tdx_access_token');
    }

    public function test_returns_503_when_not_configured(): void
    {
        config(['services.tdx.client_id' => null]);

        $this->getJson('/api/parking/nearby-availability?city=Taipei')->assertStatus(503);
    }

    public function test_rejects_unsupported_city(): void
    {
        $this->getJson('/api/parking/nearby-availability?city=NewTaipei')->assertStatus(422);
    }

    public function test_exchanges_token_and_merges_availability_with_car_park_metadata(): void
    {
        Http::fake([
            'https://tdx.transportdata.tw/auth/*' => Http::response([
                'access_token' => 'fake-access-token',
                'expires_in' => 1800,
                'token_type' => 'bearer',
            ], 200),
            '*/ParkingAvailability/City/Taipei*' => Http::response([
                'SrcUpdateTime' => '2026-08-16T10:00:00+08:00',
                'UpdateTime' => '2026-08-16T10:00:00+08:00',
                'AuthorityCode' => 'TCG',
                'Items' => [
                    [
                        'CarParkID' => 'TPE_C001',
                        'CarParkName' => ['Zh_tw' => '市民廣場地下停車場', 'En' => null],
                        'TotalSpaces' => 200,
                        'AvailableSpaces' => 37,
                        'DataCollectTime' => '2026-08-16T09:58:00+08:00',
                        'ServiceStatus' => 1,
                        'FullStatus' => 0,
                    ],
                    [
                        'CarParkID' => 'TPE_C002',
                        'CarParkName' => ['Zh_tw' => null, 'En' => null],
                        'TotalSpaces' => 80,
                        // -1 = 未知，不是 0。
                        'AvailableSpaces' => -1,
                        'DataCollectTime' => '2026-08-16T09:58:00+08:00',
                        'ServiceStatus' => 3,
                        'FullStatus' => 3,
                    ],
                ],
                'Count' => 2,
            ], 200),
            '*/CarPark/City/Taipei*' => Http::response([
                'SrcUpdateTime' => '2026-08-16T10:00:00+08:00',
                'Items' => [
                    ['CarParkID' => 'TPE_C001', 'CarParkName' => ['Zh_tw' => '市民廣場地下停車場', 'En' => 'Civic Plaza'], 'Address' => '台北市信義區市府路1號'],
                    ['CarParkID' => 'TPE_C002', 'CarParkName' => ['Zh_tw' => '中山地下停車場', 'En' => null], 'Address' => '台北市中山區中山北路二段'],
                ],
                'Count' => 2,
            ], 200),
        ]);

        $response = $this->getJson('/api/parking/nearby-availability?city=Taipei');

        $response->assertOk()->assertJson([
            [
                'id' => 'TPE_C001',
                'name' => '市民廣場地下停車場',
                'address' => '台北市信義區市府路1號',
                'availableSpaces' => 37,
                'totalSpaces' => 200,
                'serviceStatus' => '營業中',
                'fullStatus' => '尚有空位',
            ],
            [
                'id' => 'TPE_C002',
                // 缺 Zh_tw 時要 fallback 到 CarPark 端點的名稱。
                'name' => '中山地下停車場',
                'address' => '台北市中山區中山北路二段',
                // -1 要轉成 null，不能顯示成 0。
                'availableSpaces' => null,
                'totalSpaces' => 80,
                'serviceStatus' => '非營業時段',
                'fullStatus' => '過度擁擠',
            ],
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/openid-connect/token')
            && $request['grant_type'] === 'client_credentials'
            && $request['client_id'] === 'test-id');
    }

    public function test_caches_the_access_token_across_requests(): void
    {
        Http::fake([
            'https://tdx.transportdata.tw/auth/*' => Http::response(['access_token' => 'cached-token', 'expires_in' => 1800], 200),
            '*/ParkingAvailability/City/Taipei*' => Http::response(['Items' => []], 200),
            '*/CarPark/City/Taipei*' => Http::response(['Items' => []], 200),
        ]);

        $this->getJson('/api/parking/nearby-availability?city=Taipei')->assertOk();
        $this->getJson('/api/parking/nearby-availability?city=Taipei')->assertOk();

        Http::assertSentCount(5); // 1 token + (2 data calls x 2 requests) -- token only fetched once
    }
}
