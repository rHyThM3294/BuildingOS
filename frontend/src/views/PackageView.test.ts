import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import type { PackageItem } from '@/types/models'
import PackageView from './PackageView.vue'

const fetchPackages = vi.fn()
const notify = vi.fn().mockResolvedValue(undefined)
const collect = vi.fn().mockResolvedValue(undefined)
const register = vi.fn()

const packages = ref<PackageItem[]>([])
const loading = ref(false)

vi.mock('@/composables/usePackage', () => ({
  usePackage: () => ({ packages, loading, fetchPackages, notify, collect, register }),
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

describe('PackageView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    packages.value = []
    loading.value = false
  })

  it('shows a 通知住戶 button for a pending package, not 標記已領取', () => {
    packages.value = [makePackage({ status: 'pending' })]
    const wrapper = mount(PackageView)

    expect(wrapper.text()).toContain('通知住戶')
    expect(wrapper.text()).not.toContain('標記已領取')
  })

  it('shows 標記已領取 for a notified package', () => {
    packages.value = [makePackage({ status: 'notified' })]
    const wrapper = mount(PackageView)

    expect(wrapper.text()).toContain('標記已領取')
    expect(wrapper.text()).not.toContain('通知住戶')
  })

  it('shows no action button once collected', () => {
    packages.value = [makePackage({ status: 'collected' })]
    const wrapper = mount(PackageView)

    expect(wrapper.find('button.btn-ghost').exists()).toBe(false)
  })

  it('clicking 通知住戶 calls notify() with the package id', async () => {
    packages.value = [makePackage({ id: 7, status: 'pending' })]
    const wrapper = mount(PackageView)

    await wrapper.find('button.btn-ghost').trigger('click')

    expect(notify).toHaveBeenCalledWith(7)
  })

  it('shows an empty state when there are no packages', () => {
    const wrapper = mount(PackageView)

    expect(wrapper.text()).toContain('尚無包裹紀錄')
  })
})
