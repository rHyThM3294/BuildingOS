import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { authService } from '@/services/auth'
import type { User } from '@/types/models'
import { useAuthStore } from './auth'

vi.mock('@/services/auth', () => ({
  authService: {
    login: vi.fn(),
    logout: vi.fn(),
    me: vi.fn(),
  },
}))

const user: User = { id: 1, name: 'Demo', email: 'demo@buildingos.test' }

describe('useAuthStore', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('login() stores the token and user, and flips isAuthenticated', async () => {
    vi.mocked(authService.login).mockResolvedValue({ data: { token: 'tok-123', user } } as never)

    const store = useAuthStore()
    expect(store.isAuthenticated).toBe(false)

    await store.login('demo@buildingos.test', 'buildingos-demo')

    expect(store.isAuthenticated).toBe(true)
    expect(store.token).toBe('tok-123')
    expect(store.user).toEqual(user)
    expect(localStorage.getItem('buildingos_token')).toBe('tok-123')
  })

  it('logout() calls the API then clears local state even if the API call fails', async () => {
    vi.mocked(authService.login).mockResolvedValue({ data: { token: 'tok-123', user } } as never)
    vi.mocked(authService.logout).mockRejectedValue(new Error('network down'))

    const store = useAuthStore()
    await store.login('demo@buildingos.test', 'buildingos-demo')

    await expect(store.logout()).rejects.toThrow()

    expect(store.isAuthenticated).toBe(false)
    expect(store.token).toBeNull()
    expect(localStorage.getItem('buildingos_token')).toBeNull()
  })

  it('clearSession() wipes state without calling the API (used by the 401 interceptor)', () => {
    const store = useAuthStore()
    store.setToken('stale-token')

    store.clearSession()

    expect(store.isAuthenticated).toBe(false)
    expect(authService.logout).not.toHaveBeenCalled()
  })
})
