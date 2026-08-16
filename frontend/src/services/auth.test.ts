import { describe, expect, it, vi } from 'vitest'
import { http } from './http'
import { authService } from './auth'

vi.mock('./http', () => ({
  http: { get: vi.fn(), post: vi.fn() },
}))

describe('authService', () => {
  it('login() posts email/password to /login', () => {
    authService.login('demo@buildingos.test', 'buildingos-demo')

    expect(http.post).toHaveBeenCalledWith('/login', {
      email: 'demo@buildingos.test',
      password: 'buildingos-demo',
    })
  })

  it('logout() posts to /logout with no body', () => {
    authService.logout()

    expect(http.post).toHaveBeenCalledWith('/logout')
  })

  it('me() gets /user', () => {
    authService.me()

    expect(http.get).toHaveBeenCalledWith('/user')
  })
})
