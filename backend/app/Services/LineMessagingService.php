<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * 封裝 LINE Messaging API 的 push 端點呼叫。
 * 官方 OpenAPI 規格：https://github.com/line/line-openapi
 * （LINE Notify 已於 2025/3/31 停止服務，一律改用 Messaging API。）
 */
class LineMessagingService
{
    private const PUSH_ENDPOINT = 'https://api.line.me/v2/bot/message/push';

    public function pushText(string $toUserId, string $message): bool
    {
        $token = config('services.line.channel_access_token');

        if (! $token) {
            // 尚未設定金鑰時（本機/Demo 環境）直接視為成功，避免整條流程被卡住。
            logger()->info('[LineMessagingService] channel_access_token 未設定，略過真實推播', [
                'to' => $toUserId,
                'message' => $message,
            ]);

            return true;
        }

        $response = Http::withToken($token)
            ->post(self::PUSH_ENDPOINT, [
                'to' => $toUserId,
                'messages' => [
                    ['type' => 'text', 'text' => $message],
                ],
            ]);

        return $response->successful();
    }
}
