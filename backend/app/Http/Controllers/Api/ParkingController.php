<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParkingLogResource;
use App\Models\ParkingLog;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ParkingController extends Controller
{
    #[OA\Get(
        path: '/parking/logs',
        summary: '取得車輛進出紀錄',
        tags: ['Parking'],
        parameters: [
            new OA\Parameter(name: 'direction', in: 'query', schema: new OA\Schema(type: 'string', enum: ['in', 'out'])),
        ],
        responses: [
            new OA\Response(response: 200, description: '成功', content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/ParkingLog'),
            )),
        ],
    )]
    public function index(Request $request)
    {
        $logs = ParkingLog::query()
            ->when($request->query('direction'), fn ($q, $direction) => $q->where('direction', $direction))
            ->orderByDesc('recognized_at')
            ->get();

        return ParkingLogResource::collection($logs);
    }

    #[OA\Post(
        path: '/parking/recognize',
        summary: '送入車牌號碼進行辨識並登記進出紀錄',
        tags: ['Parking'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['plateNumber', 'direction'],
                properties: [
                    new OA\Property(property: 'plateNumber', type: 'string', example: 'ABC-1234'),
                    new OA\Property(property: 'direction', type: 'string', enum: ['in', 'out']),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: '辨識完成', content: new OA\JsonContent(ref: '#/components/schemas/ParkingLog')),
        ],
    )]
    public function recognize(Request $request)
    {
        $validated = $request->validate([
            'plateNumber' => ['required', 'string', 'max:20'],
            'direction' => ['required', 'in:in,out'],
        ]);

        // 尚未串接真實車牌辨識硬體/AI 服務，先以隨機結果模擬辨識流程。
        $recognized = fake()->boolean(90);

        $log = ParkingLog::create([
            'plate_number' => $validated['plateNumber'],
            'direction' => $validated['direction'],
            'status' => $recognized ? 'success' : 'failed',
            'owner_name' => $recognized ? fake()->name() : null,
            'recognized_at' => now(),
        ]);

        return (new ParkingLogResource($log))->response()->setStatusCode(201);
    }
}
