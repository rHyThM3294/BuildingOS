import { http } from './http'
import type { WeatherAlert, WeatherForecast } from '@/types/models'

/**
 * 轉發中央氣象署 (CWA) 開放資料平臺，真實外部政府 Swagger API 串接示範。
 * https://opendata.cwa.gov.tw/dist/opendata-swagger.html
 */
export const weatherService = {
  getForecast(city?: string) {
    return http.get<WeatherForecast>('/weather/forecast', { params: { city } })
  },

  getAlerts(city?: string) {
    return http.get<WeatherAlert[]>('/weather/alerts', { params: { city } })
  },
}
