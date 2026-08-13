import { describe, expect, it, vi } from 'vitest'
import { http } from './http'
import { weatherService } from './weather'

vi.mock('./http', () => ({
  http: { get: vi.fn(), post: vi.fn(), patch: vi.fn() },
}))

describe('weatherService', () => {
  it('getForecast() requests /weather/forecast with the given city', () => {
    weatherService.getForecast('臺北市')

    expect(http.get).toHaveBeenCalledWith('/weather/forecast', { params: { city: '臺北市' } })
  })

  it('getAlerts() requests /weather/alerts with the given city', () => {
    weatherService.getAlerts('新竹縣')

    expect(http.get).toHaveBeenCalledWith('/weather/alerts', { params: { city: '新竹縣' } })
  })
})
