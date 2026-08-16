import { http } from './http'
import type { ParkingLotAvailability } from '@/types/models'

/**
 * 轉發交通部 TDX 平臺的路外停車場即時空位。跟 LINE/CWA 不同的地方是
 * TDX 用 OAuth2 client_credentials，後端要自己換 token、快取、過期
 * 重新換發，前端這層完全不用管，只管拿結果。
 * https://tdx.transportdata.tw/api-service/swagger
 */
export const tdxParkingService = {
  getNearbyAvailability(city?: string) {
    return http.get<ParkingLotAvailability[]>('/parking/nearby-availability', { params: { city } })
  },
}
