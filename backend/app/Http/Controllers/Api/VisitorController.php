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
}
