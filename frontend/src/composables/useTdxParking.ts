import { ref } from 'vue'
import { tdxParkingService } from '@/services/tdxParking'
import type { ParkingLotAvailability } from '@/types/models'
import { isHttpStatus } from '@/utils/http'

export function useTdxParking() {
  const lots = ref<ParkingLotAvailability[]>([])
  const loading = ref(false)
  const notConfigured = ref(false)
  const error = ref<string | null>(null)

  async function fetchNearbyAvailability(city?: string) {
    loading.value = true
    notConfigured.value = false
    error.value = null
    try {
      const { data } = await tdxParkingService.getNearbyAvailability(city)
      lots.value = data
    } catch (e) {
      if (isHttpStatus(e, 503)) {
        notConfigured.value = true
      } else {
        error.value = '無法取得附近停車場資料'
      }
    } finally {
      loading.value = false
    }
  }

  return { lots, loading, notConfigured, error, fetchNearbyAvailability }
}
