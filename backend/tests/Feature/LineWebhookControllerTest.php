<?php

namespace Tests\Feature;

use App\Models\PackageItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * 簽章驗證邏輯：base64(hmac_sha256(channel_secret, raw_body))，跟
 * LINE 官方文件範例（openssl dgst -sha256 -hmac secret -binary | base64）
 * 算法一致，這裡直接用同樣的方式在測試裡算出正確簽章來驗證流程。
 */
class LineWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-channel-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.line.channel_secret' => self::SECRET,
            'services.line.channel_access_token' => 'test-access-token',
        ]);
    }

    private function sign(string $body): string
    {
        return base64_encode(hash_hmac('sha256', $body, self::SECRET, true));
    }

    public function test_rejects_request_with_wrong_signature(): void
    {
        $body = json_encode(['destination' => 'U123', 'events' => []]);

        $this->call('POST', '/api/line/webhook', [], [], [], [
            'HTTP_X-Line-Signature' => 'not-the-right-signature',
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertStatus(400);
    }

    public function test_accepts_empty_events_verify_ping(): void
    {
        $body = json_encode(['destination' => 'U123', 'events' => []]);

        $this->call('POST', '/api/line/webhook', [], [], [], [
            'HTTP_X-Line-Signature' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();
    }

    public function test_returns_503_when_channel_secret_not_configured(): void
    {
        config(['services.line.channel_secret' => null]);
        $body = json_encode(['destination' => 'U123', 'events' => []]);

        $this->call('POST', '/api/line/webhook', [], [], [], [
            'HTTP_X-Line-Signature' => 'irrelevant',
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertStatus(503);
    }

    public function test_replies_with_pending_package_count_when_asked(): void
    {
        PackageItem::create([
            'tracking_no' => 'T1', 'recipient_unit' => 'A-1', 'recipient_name' => 'Test',
            'status' => 'pending', 'arrived_at' => now(),
        ]);

        Http::fake(['https://api.line.me/*' => Http::response(['status' => 'ok'], 200)]);

        $body = json_encode([
            'destination' => 'U123',
            'events' => [[
                'type' => 'message',
                'replyToken' => 'reply-token-abc',
                'message' => ['type' => 'text', 'text' => '查詢包裹'],
                'source' => ['type' => 'user', 'userId' => 'Uabc'],
                'timestamp' => 1700000000000,
            ]],
        ]);

        $this->call('POST', '/api/line/webhook', [], [], [], [
            'HTTP_X-Line-Signature' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.line.me/v2/bot/message/reply'
                && $request['replyToken'] === 'reply-token-abc'
                && str_contains($request['messages'][0]['text'], '1 件包裹尚未領取');
        });
    }
}
