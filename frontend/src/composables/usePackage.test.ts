import { beforeEach, describe, expect, it, vi } from 'vitest'
import { packageService } from '@/services/package'
import type { PackageItem } from '@/types/models'
import { usePackage } from './usePackage'

vi.mock('@/services/package', () => ({
  packageService: {
    list: vi.fn(),
    register: vi.fn(),
    notify: vi.fn(),
    markCollected: vi.fn(),
  },
}))

function makePackage(overrides: Partial<PackageItem> = {}): PackageItem {
  return {
    id: 1,
    trackingNo: 'SF1234567890',
    recipientUnit: 'A-1203',
    recipientName: '陳志明',
    courier: '黑貓宅急便',
    status: 'pending',
    arrivedAt: '2026-08-13T00:00:00Z',
    collectedAt: null,
    ...overrides,
  }
}

describe('usePackage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('fetchPackages() loads the list and toggles loading', async () => {
    const list = [makePackage()]
    vi.mocked(packageService.list).mockResolvedValue({ data: list } as never)

    const { packages, loading, fetchPackages } = usePackage()
    const promise = fetchPackages()
    expect(loading.value).toBe(true)

    await promise

    expect(loading.value).toBe(false)
    expect(packages.value).toEqual(list)
  })

  it('notify() replaces only the matching package with the pending -> notified transition', async () => {
    const other = makePackage({ id: 2, status: 'pending' })
    const target = makePackage({ id: 1, status: 'pending' })
    const notified = { ...target, status: 'notified' as const }

    vi.mocked(packageService.list).mockResolvedValue({ data: [target, other] } as never)
    vi.mocked(packageService.notify).mockResolvedValue({ data: notified } as never)

    const { packages, fetchPackages, notify } = usePackage()
    await fetchPackages()
    await notify(1)

    expect(packageService.notify).toHaveBeenCalledWith(1)
    expect(packages.value.find((p) => p.id === 1)?.status).toBe('notified')
    expect(packages.value.find((p) => p.id === 2)?.status).toBe('pending')
  })

  it('register() prepends the newly created package to the list', async () => {
    const created = makePackage({ id: 99, trackingNo: 'NEW000001' })
    vi.mocked(packageService.register).mockResolvedValue({ data: created } as never)

    const { packages, register } = usePackage()
    const result = await register({
      trackingNo: 'NEW000001',
      recipientUnit: 'A-1203',
      recipientName: '陳志明',
      courier: null,
    })

    expect(result).toEqual(created)
    expect(packages.value[0]).toEqual(created)
  })
})
