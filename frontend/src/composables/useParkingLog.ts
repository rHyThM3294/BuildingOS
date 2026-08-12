import { ref } from 'vue'
import { parkingService } from '@/services/parking'
import type { ParkingLog, ParkingRecognizeRequest } from '@/types/models'

export function useParkingLog() {
  const logs = ref<ParkingLog[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchLogs() {
    loading.value = true
    error.value = null
    try {
      const { data } = await parkingService.getLogs()
      logs.value = data
    } catch {
      error.value = '讀取進出紀錄失敗'
    } finally {
      loading.value = false
    }
  }

  async function recognizePlate(payload: ParkingRecognizeRequest) {
    loading.value = true
    error.value = null
    try {
      const { data } = await parkingService.recognize(payload)
      logs.value = [data, ...logs.value]
      return data
    } catch {
      error.value = '車牌辨識失敗'
      return null
    } finally {
      loading.value = false
    }
  }

  return { logs, loading, error, fetchLogs, recognizePlate }
}
