<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageItemResource;
use App\Models\PackageItem;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PackageController extends Controller
{
    public function __construct(private readonly LineMessagingService $line)
    {
    }

    #[OA\Get(
        path: '/packages',
        operationId: 'listPackages',
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
        operationId: 'registerPackage',
        summary: '登記新到貨包裹（登記後狀態為 pending，需再呼叫 /packages/{id}/notify 通知住戶）',
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
        path: '/packages/{package}/notify',
        operationId: 'notifyPackage',
        summary: '通知住戶包裹已送達（狀態 pending → notified，並透過 LINE Messaging API 推播）',
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'package', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '通知成功', content: new OA\JsonContent(ref: '#/components/schemas/PackageItem')),
            new OA\Response(response: 422, description: '包裹不是待通知狀態'),
        ],
    )]
    public function notify(PackageItem $package)
    {
        abort_if($package->status !== 'pending', 422, '此包裹目前不是待通知狀態');

        $package->update(['status' => 'notified']);

        $userId = config('services.line.default_user_id');
        if ($userId) {
            $this->line->pushText(
                $userId,
                "您有一件包裹已送達管理室，請盡快領取。\n單號：{$package->tracking_no}（{$package->recipient_unit}）",
            );
        }

        return new PackageItemResource($package);
    }

    #[OA\Patch(
        path: '/packages/{package}/collect',
        operationId: 'collectPackage',
        summary: '標記包裹已被領取',
        tags: ['Packages'],
        parameters: [
            new OA\Parameter(name: 'package', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '更新成功', content: new OA\JsonContent(ref: '#/components/schemas/PackageItem')),
            new OA\Response(response: 422, description: '包裹尚未通知住戶'),
        ],
    )]
    public function collect(PackageItem $package)
    {
        abort_if($package->status !== 'notified', 422, '包裹尚未通知住戶，請先通知');

        $package->update([
            'status' => 'collected',
            'collected_at' => now(),
        ]);

        return new PackageItemResource($package);
    }
}
