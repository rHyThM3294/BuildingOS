<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'BuildingOS API',
    description: '智慧大樓整合管理平台 API：車輛門禁、包裹管理、訪客/外送通知',
)]
#[OA\Server(url: '/api', description: 'API server')]
#[OA\Tag(name: 'Parking', description: '車輛門禁 / 車牌辨識')]
#[OA\Tag(name: 'Packages', description: '包裹管理')]
#[OA\Tag(name: 'Visitors', description: '訪客 / 外送到達通知')]
#[OA\Tag(name: 'Notifications', description: 'LINE Messaging API 轉發')]
abstract class Controller
{
    //
}
