import { ref } from 'vue'
import { packageService } from '@/services/package'
import type { PackageItem } from '@/types/models'

export function usePackage() {
  const packages = ref<PackageItem[]>([])
  const loading = ref(false)

  async function fetchPackages() {
    loading.value = true
    try {
      const { data } = await packageService.list()
      packages.value = data
    } finally {
      loading.value = false
    }
  }

  async function collect(id: number) {
    const { data } = await packageService.markCollected(id)
    const idx = packages.value.findIndex((p) => p.id === id)
    if (idx !== -1) packages.value[idx] = data
  }

  return { packages, loading, fetchPackages, collect }
}
