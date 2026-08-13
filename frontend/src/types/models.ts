/**
 * 網域模型型別，直接從 Swagger/OpenAPI 規格產生的 api.generated.ts 取用，
 * 不手寫重複定義。後端 API 變動時的流程：
 *
 *   1. 改 Controller 的 OA\* annotation（或 route/邏輯）
 *   2. php artisan l5-swagger:generate   重新產生 swagger 文件
 *   3. npm run gen:api-types             重新產生 api.generated.ts
 *   4. tsc 若報型別錯誤，代表前端有地方還沒跟上 API 的新樣子
 */
import type { components } from './api.generated'

export type ParkingLog = components['schemas']['ParkingLog']
export type PackageItem = components['schemas']['PackageItem']
export type VisitorLog = components['schemas']['VisitorLog']
export type WeatherForecast = components['schemas']['WeatherForecast']
export type WeatherAlert = components['schemas']['WeatherAlert']

export type PlateRecognitionStatus = ParkingLog['status']
export type EntryDirection = ParkingLog['direction']
export type PackageStatus = PackageItem['status']
export type VisitorStatus = VisitorLog['status']
export type VisitorType = VisitorLog['visitorType']

export interface ParkingRecognizeRequest {
  plateNumber: string
  direction: EntryDirection
}
