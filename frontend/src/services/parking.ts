import { http } from './http'
import type { ParkingLog, ParkingRecognizeRequest } from '@/types/models'

/**
 * 對應後端自建 Swagger: POST /parking/recognize, GET /parking/logs
 * 詳細規格見 backend/ 的 L5-Swagger 文件 (/api/documentation)
 */
export const parkingService = {
  recognize(payload: ParkingRecognizeRequest) {
    return http.post<ParkingLog>('/parking/recognize', payload)
  },

  getLogs(params?: { direction?: ParkingRecognizeRequest['direction']; date?: string }) {
    return http.get<ParkingLog[]>('/parking/logs', { params })
  },
}
