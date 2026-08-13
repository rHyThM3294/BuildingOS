import { http } from './http'
import type { PackageItem, PackageStatus } from '@/types/models'

export const packageService = {
  list(params?: { status?: PackageStatus }) {
    return http.get<PackageItem[]>('/packages', { params })
  },

  register(payload: Pick<PackageItem, 'trackingNo' | 'recipientUnit' | 'recipientName' | 'courier'>) {
    return http.post<PackageItem>('/packages', payload)
  },

  notify(id: number) {
    return http.patch<PackageItem>(`/packages/${id}/notify`)
  },

  markCollected(id: number) {
    return http.patch<PackageItem>(`/packages/${id}/collect`)
  },
}
