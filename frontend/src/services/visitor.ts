import { http } from './http'
import type { VisitorLog, VisitorStatus } from '@/types/models'

export const visitorService = {
  list(params?: { status?: VisitorStatus }) {
    return http.get<VisitorLog[]>('/visitors', { params })
  },

  register(payload: Pick<VisitorLog, 'visitorName' | 'visitorType' | 'targetUnit'>) {
    return http.post<VisitorLog>('/visitors', payload)
  },

  updateStatus(id: number, status: VisitorStatus) {
    return http.patch<VisitorLog>(`/visitors/${id}/status`, { status })
  },
}
