import { ref } from 'vue'
import { visitorService } from '@/services/visitor'
import type { VisitorLog, VisitorStatus } from '@/types/models'

export function useVisitor() {
  const visitors = ref<VisitorLog[]>([])
  const loading = ref(false)

  async function fetchVisitors() {
    loading.value = true
    try {
      const { data } = await visitorService.list()
      visitors.value = data
    } finally {
      loading.value = false
    }
  }

  async function setStatus(id: number, status: VisitorStatus) {
    const { data } = await visitorService.updateStatus(id, status)
    const idx = visitors.value.findIndex((v) => v.id === id)
    if (idx !== -1) visitors.value[idx] = data
  }

  return { visitors, loading, fetchVisitors, setStatus }
}
