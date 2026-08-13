import { beforeEach, describe, expect, it, vi } from 'vitest'
import { weatherService } from '@/services/weather'
import { useWeather } from './useWeather'

vi.mock('@/services/weather', () => ({
  weatherService: {
    getForecast: vi.fn(),
    getAlerts: vi.fn(),
  },
}))

describe('useWeather', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('populates forecast and alerts on success', async () => {
    const forecast = {
      city: '臺北市',
      description: '多雲',
      minTemp: 26,
      maxTemp: 31,
      pop: 20,
      comfort: '舒適',
      startTime: '2026-08-13T00:00:00Z',
      endTime: '2026-08-13T12:00:00Z',
    }
    vi.mocked(weatherService.getForecast).mockResolvedValue({ data: forecast } as never)
    vi.mocked(weatherService.getAlerts).mockResolvedValue({ data: [] } as never)

    const { forecast: forecastRef, alerts, notConfigured, error, fetchWeather } = useWeather()
    await fetchWeather('臺北市')

    expect(forecastRef.value).toEqual(forecast)
    expect(alerts.value).toEqual([])
    expect(notConfigured.value).toBe(false)
    expect(error.value).toBeNull()
  })

  it('sets notConfigured (not a generic error) on a 503 response', async () => {
    const notConfiguredResponse = { response: { status: 503 } }
    vi.mocked(weatherService.getForecast).mockRejectedValue(notConfiguredResponse)
    vi.mocked(weatherService.getAlerts).mockRejectedValue(notConfiguredResponse)

    const { notConfigured, error, fetchWeather } = useWeather()
    await fetchWeather()

    expect(notConfigured.value).toBe(true)
    expect(error.value).toBeNull()
  })

  it('sets a generic error for non-503 failures', async () => {
    vi.mocked(weatherService.getForecast).mockRejectedValue(new Error('network down'))
    vi.mocked(weatherService.getAlerts).mockRejectedValue(new Error('network down'))

    const { notConfigured, error, fetchWeather } = useWeather()
    await fetchWeather()

    expect(notConfigured.value).toBe(false)
    expect(error.value).toBe('無法取得天氣資料')
  })
})
