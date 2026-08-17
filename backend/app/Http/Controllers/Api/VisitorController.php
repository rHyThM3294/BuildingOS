<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VisitorLogResource;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VisitorController extends Controller
{
    #[OA\Get(
        path: '/visitors',
        operationId: 'listVisitors',
        summary: '取得訪客 / 外送登記列表',
        tags: ['Visitors'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['waiting', 'notified', 'entered', 'left'])),
        ],
        responses: [
            new OA\Response(response: 200, description: '成功', content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/VisitorLog'),
            )),
        ],
    )]
    public function index(Request $request)
    {
        $visitors = VisitorLog::query()
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('registered_at')
            ->get();

        return VisitorLogResource::collection($visitors);
    }

    #[OA\Post(
        path: '/visitors',
        operationId: 'registerVisitor',
        summary: '登記訪客或外送到達',
        tags: ['Visitors'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['visitorName', 'visitorType', 'targetUnit'],
                properties: [
                    new OA\Property(property: 'visitorName', type: 'string'),
                    new OA\Property(property: 'visitorType', type: 'string', enum: ['guest', 'delivery']),
                    new OA\Property(property: 'targetUnit', type: 'string', example: 'A-1203'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: '登記成功', content: new OA\JsonContent(ref: '#/components/schemas/VisitorLog')),
        ],
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visitorName' => ['required', 'string', 'max:50'],
            'visitorType' => ['required', 'in:guest,delivery'],
            'targetUnit' => ['required', 'string', 'max:20'],
        ]);

        $visitor = VisitorLog::create([
            'visitor_name' => $validated['visitorName'],
            'visitor_type' => $validated['visitorType'],
            'target_unit' => $validated['targetUnit'],
            'status' => 'waiting',
            'registered_at' => now(),
        ]);

        return (new VisitorLogResource($visitor))->response()->setStatusCode(201);
    }

    #[OA\Patch(
        path: '/visitors/{visitor}/status',
        operationId: 'updateVisitorStatus',
        summary: '更新訪客狀態（等待中 → 已通知 → 已進入 → 已離開）',
        tags: ['Visitors'],
        parameters: [
            new OA\Parameter(name: 'visitor', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['waiting', 'notified', 'entered', 'left']),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: '更新成功', content: new OA\JsonContent(ref: '#/components/schemas/VisitorLog')),
        ],
    )]
    public function updateStatus(Request $request, VisitorLog $visitor)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:waiting,notified,entered,left'],
        ]);

        $visitor->update([
            'status' => $validated['status'],
            'notified_at' => $validated['status'] === 'notified' ? now() : $visitor->notified_at,
        ]);

        return new VisitorLogResource($visitor);
    }

    #[OA\Post(
        path: '/visitors/reset-demo',
        operationId: 'resetVisitorDemoStatuses',
        summary: '重置訪客示範資料的狀態（僅供 Demo 用）',
        description: '狀態按鈕只能往前走（等待中→已通知→已進入→已離開），示範資料被點過幾輪後會全部卡在同一個狀態。這支端點把既有的訪客紀錄隨機打散回四種狀態都有，只用來維持 Demo 畫面好看，不代表真實業務動作，因此刻意跟登入流程綁在一起（需要 Bearer token）。',
        tags: ['Visitors'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: '重置成功', content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/VisitorLog'),
            )),
        ],
    )]
    public function resetDemo()
    {
        $statuses = ['waiting', 'notified', 'entered', 'left'];
        $visitors = VisitorLog::inRandomOrder()->get();

        $visitors->each(function (VisitorLog $visitor, int $i) use ($statuses) {
            $status = $statuses[$i % count($statuses)];

            $visitor->update([
                'status' => $status,
                'notified_at' => $status === 'waiting'
                    ? null
                    : ($visitor->notified_at ?? $visitor->registered_at->copy()->addMinutes(random_int(1, 8))),
            ]);
        });

        return VisitorLogResource::collection($visitors->sortByDesc('registered_at')->values());
    }
}
