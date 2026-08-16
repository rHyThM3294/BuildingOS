<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackageItem;
use App\Models\VisitorLog;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * 接收 LINE Messaging API 主動打進來的 webhook 事件（使用者傳訊息給
 * 官方帳號）。跟其他 controller 方向相反：這支不是我們去呼叫別人的
 * API，是別人（LINE 平臺）呼叫我們，所以重點在於「驗證真的是 LINE
 * 送來的請求」而不是怎麼組請求。
 *
 * 簽章驗證：header 是 X-Line-Signature，值是「用 Channel secret 當
 * key、對 request 原始 body 做 HMAC-SHA256、再 base64」的結果。一定
 * 要用還沒被解析過的原始 body（Request::getContent()），如果自己用
 * $request->all() 重組 JSON 再驗，key 順序或跳脫字元只要跟原始 body
 * 有一點差異，簽章就會對不上。
 */
class LineWebhookController extends Controller
{
    public function __construct(private readonly LineMessagingService $line)
    {
    }

    #[OA\Post(
        path: '/line/webhook',
        operationId: 'receiveLineWebhook',
        summary: '接收 LINE 官方帳號的 webhook 事件（由 LINE 平臺呼叫，不是前端呼叫）',
        description: '需在 LINE Developers Console 的 Messaging API 設定頁把 Webhook URL 指到這支端點，並開啟「Use webhook」。請求會帶 X-Line-Signature header，本端點會用 Channel secret 驗證來源真偽後才處理事件。',
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'X-Line-Signature', in: 'header', required: true, description: 'HMAC-SHA256(Channel secret, raw body) 的 base64 結果', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: '已處理（含簽章驗證通過但沒有事件的健康檢查請求）'),
            new OA\Response(response: 400, description: '簽章驗證失敗'),
            new OA\Response(response: 503, description: '後端尚未設定 LINE_CHANNEL_SECRET'),
        ],
    )]
    public function handle(Request $request)
    {
        $secret = config('services.line.channel_secret');
        if (! $secret) {
            return response()->json(['message' => '尚未設定 LINE_CHANNEL_SECRET，無法驗證 webhook 來源，拒絕處理。'], 503);
        }

        $rawBody = $request->getContent();
        $signature = $request->header('x-line-signature', '');
        $expected = base64_encode(hash_hmac('sha256', $rawBody, $secret, true));

        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => '簽章驗證失敗'], 400);
        }

        // LINE 在 Console 按「Verify」時會送一個 events: [] 的健康檢查請求，
        // 一定要回 200，不然 webhook 設定畫面會顯示失敗。
        foreach ($request->input('events', []) as $event) {
            $this->handleEvent($event);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleEvent(array $event): void
    {
        if (($event['type'] ?? null) !== 'message' || ($event['message']['type'] ?? null) !== 'text') {
            return;
        }

        $replyToken = $event['replyToken'] ?? null;
        $text = $event['message']['text'] ?? '';
        if (! $replyToken) {
            return;
        }

        $this->line->replyText($replyToken, $this->buildReply($text));
    }

    private function buildReply(string $text): string
    {
        if (str_contains($text, '包裹')) {
            $pending = PackageItem::where('status', '!=', 'collected')->count();

            return "目前有 {$pending} 件包裹尚未領取。";
        }

        if (str_contains($text, '訪客') || str_contains($text, '外送')) {
            $waiting = VisitorLog::whereIn('status', ['waiting', 'notified'])->count();

            return "目前有 {$waiting} 筆訪客/外送登記尚未完成（等待中或已通知）。";
        }

        return "收到你的訊息：「{$text}」\n這是 BuildingOS 的 demo 機器人，可以問我「包裹」或「訪客」查詢目前狀態。";
    }
}
