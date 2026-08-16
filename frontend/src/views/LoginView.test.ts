import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import LoginView from './LoginView.vue'

const push = vi.fn()
const login = vi.fn()
let routeQuery: Record<string, string> = {}

vi.mock('vue-router', () => ({
  useRoute: () => ({ query: routeQuery }),
  useRouter: () => ({ push }),
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ login }),
}))

describe('LoginView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    routeQuery = {}
  })

  it('logs in with the pre-filled demo credentials and redirects to /', async () => {
    login.mockResolvedValue(undefined)
    const wrapper = mount(LoginView)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(login).toHaveBeenCalledWith('demo@buildingos.test', 'buildingos-demo')
    expect(push).toHaveBeenCalledWith('/')
  })

  it('redirects back to the page that triggered the 401, if any', async () => {
    routeQuery = { redirect: '/packages' }
    login.mockResolvedValue(undefined)
    const wrapper = mount(LoginView)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(push).toHaveBeenCalledWith('/packages')
  })

  it('shows an error message and does not redirect on invalid credentials', async () => {
    login.mockRejectedValue(new Error('422'))
    const wrapper = mount(LoginView)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.text()).toContain('帳號或密碼錯誤')
    expect(push).not.toHaveBeenCalled()
  })
})

function flushPromises() {
  return new Promise((resolve) => setTimeout(resolve, 0))
}
