<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 前端把 API Resource 的回應當成「本體就是陣列/物件」在讀，
        // 關掉 Laravel 預設的 {"data": ...} 包裝，維持扁平結構。
        JsonResource::withoutWrapping();
    }
}
