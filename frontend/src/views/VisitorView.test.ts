import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import type { VisitorLog } from '@/types/models'
import VisitorView from './VisitorView.vue'

const fetchVisitors = vi.fn()
const setStatus = vi.fn()
const register = vi.fn()
const resetDemo = vi.fn().mockResolvedValue(undefined)
const visitors = ref<VisitorLog[]>([])
const loading = ref(false)
let isAuthenticated = false

vi.mock('@/composables/useVisitor', () => ({
  useVisitor: () => ({ visitors, loading, fetchVisitors, setStatus, register, resetDemo }),
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({
    get isAuthenticated() {
      return isAuthenticated
    },
  }),
}))

describe('VisitorView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    visitors.value = []
    loading.value = false
    isAuthenticated = false
  })

  it('hides the 重置示範資料 button when logged out', () => {
    const wrapper = mount(VisitorView)

    expect(wrapper.text()).not.toContain('重置示範資料')
  })

  it('shows the 重置示範資料 button when logged in, and calls resetDemo() on click', async () => {
    isAuthenticated = true
    const wrapper = mount(VisitorView)

    await wrapper.find('button[title*="Demo"]').trigger('click')

    expect(resetDemo).toHaveBeenCalled()
  })
})
