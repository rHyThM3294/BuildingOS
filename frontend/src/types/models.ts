/**
 * 依 Swagger/OpenAPI schema 對應的網域模型型別。
 * 待後端 L5-Swagger 文件穩定後，可改用 `npm run gen:api-types`
 * (openapi-typescript) 從 swagger.json 自動產生，取代此手寫版本。
 */

export type PlateRecognitionStatus = 'success' | 'failed'
export type EntryDirection = 'in' | 'out'

export interface ParkingLog {
  id: number
  plateNumber: string
  direction: EntryDirection
  status: PlateRecognitionStatus
  ownerName: string | null
  recognizedAt: string // ISO datetime
}

export interface ParkingRecognizeRequest {
  plateNumber: string
  direction: EntryDirection
}

export type PackageStatus = 'pending' | 'notified' | 'collected'

export interface PackageItem {
  id: number
  trackingNo: string
  recipientUnit: string
  recipientName: string
  courier: string | null
  status: PackageStatus
  arrivedAt: string
  collectedAt: string | null
}

export type VisitorStatus = 'waiting' | 'notified' | 'entered' | 'left'
export type VisitorType = 'guest' | 'delivery'

export interface VisitorLog {
  id: number
  visitorName: string
  visitorType: VisitorType
  targetUnit: string
  status: VisitorStatus
  registeredAt: string
  notifiedAt: string | null
}
