<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 純 API 專案沒有叫 "login" 的網頁路由。沒設定這個的話，
        // auth:sanctum 擋到「沒帶 Accept: application/json header」的
        // 未登入請求時，會想 redirect 到 route('login')，但那個命名路由
        // 根本不存在，反而整個噴成 500，蓋掉了原本該回的 401。
        Authenticate::redirectUsing(fn () => null);

        // Railway 用反向代理把外部的 HTTPS 終止在邊界，轉成內部 HTTP
        // 送進這台服務，Laravel 本身看到的是一個普通 HTTP request。
        // 沒設定信任代理的話，url()/asset() 產生的資源網址一律是
        // http://，讓瀏覽器把 Swagger UI 的 CSS/JS 當成 Mixed Content
        // 擋掉（畫面空白、SwaggerUIBundle is not defined）。信任代理
        // 送來的 X-Forwarded-Proto 之類 header，才能正確產生 https://。
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
