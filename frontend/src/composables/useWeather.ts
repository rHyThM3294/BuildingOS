import { ref } from 'vue'
import { weatherService } from '@/services/weather'
import type { WeatherAlert, WeatherForecast } from '@/types/models'

export function useWeather() {
  const forecast = ref<WeatherForecast | null>(null)
  const alerts = ref<WeatherAlert[]>([])
  const loading = ref(false)
  const notConfigured = ref(false)
  const error = ref<string | null>(null)

  async function fetchWeather(city?: string) {
    loading.value = true
    notConfigured.value = false
    error.value = null
    try {
      const [forecastRes, alertsRes] = await Promise.all([
        weatherService.getForecast(city),
        weatherService.getAlerts(city),
      ])
      forecast.value = forecastRes.data
      alerts.value = alertsRes.data
    } catch (e) {
      if (isAxiosStatus(e, 503)) {
        notConfigured.value = true
      } else {
        error.value = '無法取得天氣資料'
      }
    } finally {
      loading.value = false
    }
  }

  return { forecast, alerts, loading, notConfigured, error, fetchWeather }
}

function isAxiosStatus(e: unknown, status: number): boolean {
  return typeof e === 'object' && e !== null && 'response' in e && (e as { response?: { status?: number } }).response?.status === status
}
