<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    public function __construct(private readonly LineMessagingService $line)
    {
    }

    #[OA\Post(
        path: '/notifications/line',
        operationId: 'sendLineMessage',
        summary: '透過 LINE Messaging API 推播訊息給住戶（包裹到貨 / 訪客到達通知）',
        tags: ['Notifications'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['targetUnit', 'message'],
                properties: [
                    new OA\Property(property: 'targetUnit', type: 'string', example: 'A-1203'),
                    new OA\Property(property: 'message', type: 'string', example: '您有一件包裹已送達管理室，請盡快領取。'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: '已送出', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'success', type: 'boolean')],
            )),
        ],
    )]
    public function sendLineMessage(Request $request)
    {
        $validated = $request->validate([
            'targetUnit' => ['required', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:500'],
        ]);

        // Demo 階段先推給單一測試帳號；正式版應依 targetUnit 查住戶對應的 LINE userId。
        $userId = config('services.line.default_user_id');

        $success = $userId
            ? $this->line->pushText($userId, $validated['message'])
            : true;

        return response()->json(['success' => $success]);
    }
}
