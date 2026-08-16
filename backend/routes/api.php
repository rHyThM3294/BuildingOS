<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LineWebhookController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\TdxParkingController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\WeatherController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'me'])->middleware('auth:sanctum');

// 其餘 demo 端點刻意不加 auth 中介層，方便 Swagger UI 直接測試、線上
// demo 不用登入就能操作；/user 是唯一實際掛 auth:sanctum 的端點，用來
// 展示 401 攔截器（沒帶 token 或 token 失效時打這支會回 401）。
Route::get('/parking/logs', [ParkingController::class, 'index']);
Route::post('/parking/recognize', [ParkingController::class, 'recognize']);
Route::get('/parking/nearby-availability', [TdxParkingController::class, 'index']);

Route::get('/packages', [PackageController::class, 'index']);
Route::post('/packages', [PackageController::class, 'store']);
Route::patch('/packages/{package}/notify', [PackageController::class, 'notify']);
Route::patch('/packages/{package}/collect', [PackageController::class, 'collect']);

Route::get('/visitors', [VisitorController::class, 'index']);
Route::post('/visitors', [VisitorController::class, 'store']);
Route::patch('/visitors/{visitor}/status', [VisitorController::class, 'updateStatus']);

Route::post('/notifications/line', [NotificationController::class, 'sendLineMessage']);
Route::post('/line/webhook', [LineWebhookController::class, 'handle']);

Route::get('/weather/forecast', [WeatherController::class, 'forecast']);
Route::get('/weather/alerts', [WeatherController::class, 'alerts']);
