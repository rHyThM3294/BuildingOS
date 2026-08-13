<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\VisitorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Demo 階段先不加 auth 中介層，方便 Swagger UI 直接測試。
// 正式串接時應加上 ->middleware('auth:sanctum') 並要求前端帶 Bearer token。
Route::get('/parking/logs', [ParkingController::class, 'index']);
Route::post('/parking/recognize', [ParkingController::class, 'recognize']);

Route::get('/packages', [PackageController::class, 'index']);
Route::post('/packages', [PackageController::class, 'store']);
Route::patch('/packages/{package}/notify', [PackageController::class, 'notify']);
Route::patch('/packages/{package}/collect', [PackageController::class, 'collect']);

Route::get('/visitors', [VisitorController::class, 'index']);
Route::post('/visitors', [VisitorController::class, 'store']);
Route::patch('/visitors/{visitor}/status', [VisitorController::class, 'updateStatus']);

Route::post('/notifications/line', [NotificationController::class, 'sendLineMessage']);
