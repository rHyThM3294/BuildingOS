<?php

namespace App\Services;

use App\Exceptions\TdxNotConfiguredException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * TDX 用的是 OAuth2 client_credentials 流程，token 端點是 Keycloak
 * （不是 tdx.transportdata.tw 自己的網域，且必須帶 /auth 這一段路徑，
 * 少了會回 405）。跟 CWA/LINE 那種單一 API key 不一樣，這裡要自己
 * 換 token、快取、並在快到期前重新換發，不能每次呼叫都重新登入。
 */
class TdxAuthService
{
    private const TOKEN_URL = 'https://tdx.transportdata.tw/auth/realms/TDXConnect/protocol/openid-connect/token';
    private const CACHE_KEY = 'tdx_access_token';

    public function getAccessToken(): string
    {
        $clientId = config('services.tdx.client_id');
        $clientSecret = config('services.tdx.client_secret');

        if (! $clientId || ! $clientSecret) {
            throw new TdxNotConfiguredException();
        }

        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('TDX 換發 token 失敗：'.$response->body());
        }

        $token = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 1800);

        // 提前 60 秒過期，避免請求送到一半 token 剛好失效。
        Cache::put(self::CACHE_KEY, $token, max($expiresIn - 60, 60));

        return $token;
    }
}
