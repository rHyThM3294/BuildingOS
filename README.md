# BuildingOS — 智慧大樓整合管理平台

面試作品：模擬「多套子系統整合」的智慧大樓管理平台，證明看懂並串接 Swagger/OpenAPI 的能力，涵蓋車輛門禁、包裹管理、訪客/外送到達通知三個模組。

**線上 Demo**
- 前端 (Vercel)：https://buildingos-lac.vercel.app
- 後端 API / Swagger UI (Railway)：https://backend-production-be4a.up.railway.app/api/documentation

## 專案定位

對應真實產品線的常見形態：多個子系統（門禁、通知、後台管理）透過 API 串接在一起的整合平台。這個 Demo 刻意展示兩種 Swagger 串接情境：

- **串接別人寫好的 Swagger**，而且刻意涵蓋不同的認證方式與資料方向：
  - 包裹/訪客到達的通知功能串接 [LINE Messaging API](https://github.com/line/line-openapi)（Bearer token，LINE Notify 已於 2025/3/31 停止服務，官方導向 Messaging API）。
  - Dashboard 的天氣卡片串接[中央氣象署 (CWA) 開放資料平臺](https://opendata.cwa.gov.tw/dist/opendata-swagger.html)（query-param API key），真實政府機關的 Swagger/OpenAPI 文件，官方 response example 並不完整，得靠實際呼叫驗證欄位。
  - 車輛門禁頁的「附近公共停車場即時空位」串接[交通部 TDX 平臺](https://tdx.transportdata.tw/api-service/swagger)（**OAuth2 client_credentials**，要自己換 token、快取、過期前重新換發，不是單純帶一把 key），並合併兩支端點（即時空位 + 停車場metadata）成一份好讀的資料。
  - LINE Webhook（`POST /api/line/webhook`）是反過來——由 LINE 平臺主動呼叫我們，重點在**驗證來源真偽**：對 request 原始 body 做 HMAC-SHA256（key 是 Channel secret）算出簽章，跟 `X-Line-Signature` header 比對，通過才處理事件並用 replyToken 回覆。
- **自己設計 Swagger**：車牌辨識、包裹、進出紀錄目前沒有公開 API，因此自建 Laravel 後端，用 `darkaonline/l5-swagger`（zircote/swagger-php attributes）產生 OpenAPI 文件。

## 目錄結構

```
BuildingOS/
├── frontend/   Vue3 + Vite + TypeScript
└── backend/    Laravel 11 + L5-Swagger
```

## 技術棧

| 層 | 技術 |
|---|---|
| 前端 | Vue 3 (Composition API) + Vite + TypeScript + Pinia + Vue Router + Axios |
| 後端 | PHP 8.4 + Laravel 11 + L5-Swagger (OpenAPI 3) + SQLite |
| 外部整合 | LINE Messaging API（push + webhook 簽章驗證）、中央氣象署開放資料 API、交通部 TDX 平臺（OAuth2 client_credentials） |

## 前端分層架構（為 App 化預留擴充）

```
frontend/src/
├── services/       API 呼叫層，元件不直接 import axios
│   ├── http.ts       axios instance + interceptors
│   ├── parking.ts     車輛門禁 API
│   ├── package.ts     包裹管理 API
│   ├── visitor.ts     訪客/外送 API
│   └── notify.ts      LINE 通知轉發 API
├── composables/     商業邏輯 hook 化，UI 元件只負責畫面
├── stores/          Pinia（全域使用者狀態等）
├── platform/        平台抽象層 — 這是未來 App 化的關鍵
│   ├── camera.ts      拍照介面；Web 版用 <input type="file" capture">，
│   │                  未來 Capacitor 版只需新增實作，呼叫端不用改
│   └── push.ts        通知介面；Web 版用瀏覽器 Notification API，
│                      未來可換成 @capacitor/push-notifications
├── views/           頁面
├── router/          Vue Router
└── types/models.ts  依 Swagger schema 對應的 TS 型別
```

**App 擴充方式**：日後要用 [Capacitor](https://capacitorjs.com/) 把此 Vue3 專案包成 iOS/Android App 時，`services/`、`composables/`、`stores/` 幾乎不用改（業務邏輯與 UI 完全分離）；只需在 `platform/` 新增對應平台的實作（如 `camera.capacitor.ts`），並依 `Capacitor.isNativePlatform()` 切換即可。

## 後端模組

| 模組 | Model | Controller | 端點 |
|---|---|---|---|
| 車輛門禁 | `ParkingLog` | `Api/ParkingController` | `GET /api/parking/logs`、`POST /api/parking/recognize` |
| 包裹管理 | `PackageItem` | `Api/PackageController` | `GET /api/packages`、`POST /api/packages`、`PATCH /api/packages/{id}/collect` |
| 訪客/外送 | `VisitorLog` | `Api/VisitorController` | `GET /api/visitors`、`POST /api/visitors`、`PATCH /api/visitors/{id}/status` |
| LINE 通知轉發 | — | `Api/NotificationController` | `POST /api/notifications/line` |
| LINE Webhook（接收） | — | `Api/LineWebhookController` | `POST /api/line/webhook` |
| 天氣資訊（CWA） | — | `Api/WeatherController` | `GET /api/weather/forecast`、`GET /api/weather/alerts` |
| 附近停車場（TDX） | — | `Api/TdxParkingController` | `GET /api/parking/nearby-availability` |

車牌辨識目前以 `fake()->boolean(90)` 模擬辨識結果（90% 成功率），之後可替換為真實 AI 辨識服務，前端呼叫端完全不用改。

OpenAPI schema 定義集中在 `backend/app/OpenApi/Schemas/`，與 Controller 分開，方便維護。

## 開發環境設定

### 後端 (Laravel)

```bash
cd backend
composer install
cp .env.example .env      # 已預先配置好；若重新建立需 php artisan key:generate
php artisan migrate
php artisan l5-swagger:generate
php artisan serve         # http://localhost:8000
```

Swagger UI：http://localhost:8000/api/documentation

若要實測 LINE 推播，於 [LINE Developers Console](https://developers.line.biz/) 建立 Messaging API channel，把 Channel Access Token 與測試用 `userId` 填入 `backend/.env` 的 `LINE_CHANNEL_ACCESS_TOKEN`、`LINE_DEFAULT_USER_ID`；未填寫時會自動略過真實推播（只寫 log），方便本機開發。

若要實測天氣卡片，到[中央氣象署會員專區](https://opendata.cwa.gov.tw/user/authkey)申請免費金鑰，填入 `CWA_API_KEY`（`CWA_DEFAULT_CITY` 預設「臺北市」）。沒填的話 API 會回 503，前端會顯示「尚未設定金鑰」的提示卡片，而不是報錯或顯示假資料——這個行為在 `tests/Feature/WeatherControllerTest.php` 裡有測試覆蓋，包含用真實觀察到的 CWA 回應格式模擬的 parsing 測試（CWA 官方 Swagger UI 上的 response example 並不完整，實際欄位是逐一呼叫過 API 才確認的）。

若要實測附近停車場空位，到 [TDX 平臺會員中心](https://tdx.transportdata.tw/user/dataservice/apply)申請免費金鑰，填入 `TDX_CLIENT_ID`、`TDX_CLIENT_SECRET`（`TDX_DEFAULT_CITY` 預設 `Taipei`，注意是英文代碼不是中文；即時空位端點目前只有 12 個縣市有資料，不含 `NewTaipei`）。`TdxAuthService` 會自己用 client_credentials 換 token、快取到 `expires_in - 60` 秒過期，過期前不會重新登入。

若要實測 LINE Webhook（使用者傳訊息給官方帳號會觸發），除了原本的 `LINE_CHANNEL_ACCESS_TOKEN`，還要在 `backend/.env` 填入 `LINE_CHANNEL_SECRET`（LINE Developers Console → Basic settings → Channel secret，跟 access token 是不同的值），並在 Console 的 Messaging API 設定頁把 Webhook URL 指到 `{後端網址}/api/line/webhook`、開啟「Use webhook」。可以傳「查詢包裹」或「查詢訪客」給機器人測試。

### 前端 (Vue3 + Vite)

```bash
cd frontend
npm install
npm run dev                # http://localhost:5173，/api 會 proxy 到後端 :8000
npm run test                 # Vitest 單元測試
npm run build                # 正式環境打包
npm run build:staging        # staging 環境打包 (.env.staging)
```

多環境設定：`.env.development` / `.env.staging` / `.env.production` 各自帶不同的 `VITE_API_BASE_URL`。

### 前端測試

用 Vitest + @vue/test-utils，測試分兩層：

- **services**（`src/services/*.test.ts`）：mock `http`，驗證每支 API 呼叫的 URL/參數是否正確。
- **composables**（`src/composables/*.test.ts`）：mock service 層，驗證商業邏輯——例如 `usePackage` 的 `notify()` 只會更新對應那筆包裹的狀態、`useWeather` 在收到 503 時會走「尚未設定金鑰」的分支而不是當成一般錯誤。
- **元件**（`src/views/PackageView.test.ts`）：mock composable，用 `@vue/test-utils` 掛載元件，驗證「待通知才顯示通知按鈕、按下去會呼叫對應函式」這種畫面狀態機邏輯。

### 型別是從 Swagger 規格產生的，不是手寫

`frontend/src/types/models.ts` 沒有手寫任何欄位，全部 `import type { components } from './api.generated'`，而 `api.generated.ts` 是用 [openapi-typescript](https://openapi-ts.dev/) 直接讀後端 `/docs`（L5-Swagger 產生的 OpenAPI JSON）產生的。也就是說前端的型別跟 Swagger 文件是同一份來源，不會兩邊各寫一次、慢慢對不上。

改 API 時的流程：

```bash
# 1. 改 backend Controller 的邏輯 / OA\* annotation
cd backend && php artisan l5-swagger:generate   # 2. 重新產生 swagger 文件
cd ../frontend && npm run gen:api-types          # 3. 重新產生 TS 型別
npx vue-tsc --noEmit                             # 4. 型別對不上的地方會直接紅字
```

`api.generated.ts` 有 commit 進 repo（跟 lockfile 一樣，改了 API 就重新產生、一起 commit），這樣 clone 下來直接 `npm install && npm run build` 就能動，不用先啟動後端。

## 規劃中

- 住戶登入（Sanctum token，已安裝但尚未串接前端）
- 住戶對應 LINE userId 的資料表（目前 Demo 用單一測試帳號）
- 車牌辨識串接真實 AI 服務
- Capacitor App 化（`platform/` 已預留擴充點）
