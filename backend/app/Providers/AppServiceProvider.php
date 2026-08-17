<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
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

        // 保險：即使 trustProxies 判斷 X-Forwarded-Proto 出了差錯
        // （例如 Railway 內部健康檢查沒帶那個 header），正式環境還是
        // 強制用 https 產生網址，避免 Swagger UI 資源又變回 http://
        // 被瀏覽器當 Mixed Content 擋掉。
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
