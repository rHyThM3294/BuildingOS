import { beforeEach, describe, expect, it, vi } from 'vitest'

const push = vi.fn()
const clearSession = vi.fn()
let isAuthenticated = true

vi.mock('@/router', () => ({
  router: {
    push,
    currentRoute: { value: { name: 'dashboard', fullPath: '/packages' } },
  },
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({
    get isAuthenticated() {
      return isAuthenticated
    },
    clearSession,
  }),
}))

/**
 * axios 沒有公開 API 可以「拿到剛剛註冊的攔截器」來單獨呼叫，這裡用
 * interceptors.response 內部的 handlers 陣列直接抓出我們在 http.ts
 * 註冊的 rejected callback，繞過真的發一個網路請求的複雜度，單純測
 * 這段邏輯本身：401 該做什麼、不該做什麼。
 */
async function getRejectedHandler() {
  const { http } = await import('./http')
  const handlers = (http.interceptors.response as unknown as { handlers: Array<{ rejected: (e: unknown) => Promise<unknown> }> }).handlers
  return handlers[handlers.length - 1].rejected
}

describe('http 401 interceptor', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    isAuthenticated = true
  })

  it('clears the session and redirects to /login on a 401 when previously authenticated', async () => {
    const rejected = await getRejectedHandler()

    await rejected({ response: { status: 401 } }).catch(() => {})

    expect(clearSession).toHaveBeenCalled()
    expect(push).toHaveBeenCalledWith({ name: 'login', query: { redirect: '/packages' } })
  })

  it('still clears the session but does not redirect if the request was never authenticated', async () => {
    isAuthenticated = false
    const rejected = await getRejectedHandler()

    await rejected({ response: { status: 401 } }).catch(() => {})

    expect(clearSession).toHaveBeenCalled()
    expect(push).not.toHaveBeenCalled()
  })

  it('leaves the session alone for non-401 errors', async () => {
    const rejected = await getRejectedHandler()

    await rejected({ response: { status: 500 } }).catch(() => {})

    expect(clearSession).not.toHaveBeenCalled()
    expect(push).not.toHaveBeenCalled()
  })

  it('re-rejects the original error so callers still see the failure', async () => {
    const rejected = await getRejectedHandler()
    const error = { response: { status: 401 } }

    await expect(rejected(error)).rejects.toBe(error)
  })
})
