import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import type { ParkingLog, ParkingLotAvailability } from '@/types/models'
import ParkingView from './ParkingView.vue'

const fetchLogs = vi.fn()
const recognizePlate = vi.fn()
const logs = ref<ParkingLog[]>([])
const logsLoading = ref(false)
const logsError = ref<string | null>(null)

const fetchNearbyAvailability = vi.fn()
const lots = ref<ParkingLotAvailability[]>([])
const lotsLoading = ref(false)
const lotsNotConfigured = ref(false)
const lotsError = ref<string | null>(null)

vi.mock('@/composables/useParkingLog', () => ({
  useParkingLog: () => ({ logs, loading: logsLoading, error: logsError, fetchLogs, recognizePlate }),
}))

vi.mock('@/composables/useTdxParking', () => ({
  useTdxParking: () => ({
    lots,
    loading: lotsLoading,
    notConfigured: lotsNotConfigured,
    error: lotsError,
    fetchNearbyAvailability,
  }),
}))

function makeLot(overrides: Partial<ParkingLotAvailability> = {}): ParkingLotAvailability {
  return {
    id: 'TPE_C001',
    name: '市民廣場地下停車場',
    address: '台北市信義區市府路1號',
    availableSpaces: 37,
    totalSpaces: 200,
    serviceStatus: '營業中',
    fullStatus: '尚有空位',
    updatedAt: '2026-08-16T09:58:00+08:00',
    ...overrides,
  }
}

describe('ParkingView — 附近公共停車場即時空位', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    lots.value = []
    lotsLoading.value = false
    lotsNotConfigured.value = false
    lotsError.value = null
  })

  it('shows the not-configured hint with a link when TDX credentials are missing', () => {
    lotsNotConfigured.value = true
    const wrapper = mount(ParkingView)

    expect(wrapper.text()).toContain('TDX_CLIENT_ID')
    expect(wrapper.find('a[href*="tdx.transportdata.tw"]').exists()).toBe(true)
  })

  it('renders lot name, spaces, and a success badge when there is availability', () => {
    lots.value = [makeLot()]
    const wrapper = mount(ParkingView)

    expect(wrapper.text()).toContain('市民廣場地下停車場')
    expect(wrapper.text()).toContain('37')
    expect(wrapper.text()).toContain('200')
    expect(wrapper.find('.badge-success').exists()).toBe(true)
  })

  it('shows 未回報 instead of 0 when availableSpaces is null (TDX -1 = unknown)', () => {
    lots.value = [makeLot({ availableSpaces: null })]
    const wrapper = mount(ParkingView)

    expect(wrapper.text()).toContain('未回報')
  })

  it('uses a danger badge for a full lot', () => {
    lots.value = [makeLot({ fullStatus: '車位已滿' })]
    const wrapper = mount(ParkingView)

    expect(wrapper.find('.badge-danger').exists()).toBe(true)
  })
})
