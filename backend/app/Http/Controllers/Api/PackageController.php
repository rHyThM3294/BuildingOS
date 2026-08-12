<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageItemResource;
use App\Models\PackageItem;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PackageController extends Controller
{
    #[OA\Get(
        path: '/packages',
        summary: '取得包裹列表',
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['pending', 'notified', 'collected'])),
        ],
        responses: [
            new OA\Response(response: 200, description: '成功', content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/PackageItem'),
            )),
        ],
    )]
    public function index(Request $request)
    {
        $packages = PackageItem::query()
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('arrived_at')
            ->get();

        return PackageItemResource::collection($packages);
    }

    #[OA\Post(
        path: '/packages',
        summary: '登記新到貨包裹（登記後可另呼叫 /notifications/line 通知住戶）',
        tags: ['Packages'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['trackingNo', 'recipientUnit', 'recipientName'],
                properties: [
                    new OA\Property(property: 'trackingNo', type: 'string'),
                    new OA\Property(property: 'recipientUnit', type: 'string', example: 'A-1203'),
                    new OA\Property(property: 'recipientName', type: 'string'),
                    new OA\Property(property: 'courier', type: 'string', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: '登記成功', content: new OA\JsonContent(ref: '#/components/schemas/PackageItem')),
        ],
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trackingNo' => ['required', 'string', 'max:50'],
            'recipientUnit' => ['required', 'string', 'max:20'],
            'recipientName' => ['required', 'string', 'max:50'],
            'courier' => ['nullable', 'string', 'max:50'],
        ]);

        $package = PackageItem::create([
            'tracking_no' => $validated['trackingNo'],
            'recipient_unit' => $validated['recipientUnit'],
            'recipient_name' => $validated['recipientName'],
            'courier' => $validated['courier'] ?? null,
            'status' => 'pending',
            'arrived_at' => now(),
        ]);

        return (new PackageItemResource($package))->response()->setStatusCode(201);
    }

    #[OA\Patch(
        path: '/packages/{package}/collect',
        summary: '標記包裹已被領取',
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'package', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '更新成功', content: new OA\JsonContent(ref: '#/components/schemas/PackageItem')),
        ],
    )]
    public function collect(PackageItem $package)
    {
        $package->update([
            'status' => 'collected',
            'collected_at' => now(),
        ]);

        return new PackageItemResource($package);
    }
}
