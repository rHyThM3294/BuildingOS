# BuildingOS — 智慧大樓整合管理平台

面試作品：模擬「多套子系統整合」的智慧大樓管理平台，證明看懂並串接 Swagger/OpenAPI 的能力，涵蓋車輛門禁、包裹管理、訪客/外送到達通知三個模組。

**線上 Demo**
- 前端 (Vercel)：https://buildingos-lac.vercel.app
- 後端 API / Swagger UI (Railway)：https://backend-production-be4a.up.railway.app/api/documentation

## 專案定位

對應真實產品線的常見形態：多個子系統（門禁、通知、後台管理）透過 API 串接在一起的整合平台。這個 Demo 刻意展示兩種 Swagger 串接情境：

- **串接別人寫好的 Swagger**：包裹/訪客到達的通知功能串接 [LINE Messaging API](https://github.com/line/line-openapi)（LINE Notify 已於 2025/3/31 停止服務，官方導向 Messaging API）。
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
| 外部整合 | LINE Messaging API（push 訊息） |

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

### 前端 (Vue3 + Vite)

```bash
cd frontend
npm install
npm run dev                # http://localhost:5173，/api 會 proxy 到後端 :8000
npm run gen:api-types       # 依後端 Swagger 產生 TS 型別 (openapi-typescript)
npm run build                # 正式環境打包
npm run build:staging        # staging 環境打包 (.env.staging)
```

多環境設定：`.env.development` / `.env.staging` / `.env.production` 各自帶不同的 `VITE_API_BASE_URL`。

## 規劃中

- 住戶登入（Sanctum token，已安裝但尚未串接前端）
- 住戶對應 LINE userId 的資料表（目前 Demo 用單一測試帳號）
- 車牌辨識串接真實 AI 服務
- Capacitor App 化（`platform/` 已預留擴充點）
