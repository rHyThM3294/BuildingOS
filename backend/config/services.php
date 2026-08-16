<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // LINE Messaging API（LINE Notify 已於 2025/3 停止服務，改用官方
    // Messaging API push 端點：https://api.line.me/v2/bot/message/push
    // OpenAPI 規格參考：https://github.com/line/line-openapi
    'line' => [
        'channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN'),
        // 目前先用固定測試 user id 示範單一住戶推播；正式版應在
        // 住戶資料表存各自的 LINE userId 並依 target_unit 查找。
        'default_user_id' => env('LINE_DEFAULT_USER_ID'),
        // Webhook 簽章驗證用的 Channel secret（LINE Developers Console
        // → Basic settings → Channel secret），跟 access token 是不同的值。
        'channel_secret' => env('LINE_CHANNEL_SECRET'),
    ],

    // 中央氣象署 (CWA) 開放資料平臺：https://opendata.cwa.gov.tw
    // 免費會員申請金鑰：https://opendata.cwa.gov.tw/user/authkey
    // Swagger/OpenAPI 文件：https://opendata.cwa.gov.tw/dist/opendata-swagger.html
    'cwa' => [
        'api_key' => env('CWA_API_KEY'),
        'default_city' => env('CWA_DEFAULT_CITY', '臺北市'),
    ],

    // 交通部運輸資料流通服務平臺 (TDX)：https://tdx.transportdata.tw
    // 免費會員金鑰申請：https://tdx.transportdata.tw/user/dataservice/apply
    // OAuth2 client_credentials，token 端點是 Keycloak，不是 TDX 自己的網域。
    'tdx' => [
        'client_id' => env('TDX_CLIENT_ID'),
        'client_secret' => env('TDX_CLIENT_SECRET'),
        'default_city' => env('TDX_DEFAULT_CITY', 'Taipei'),
    ],

];
