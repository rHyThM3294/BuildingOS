import { http } from './http'
import type { User } from '@/types/models'

export interface LoginResponse {
  token: string
  user: User
}

export const authService = {
  login(email: string, password: string) {
    return http.post<LoginResponse>('/login', { email, password })
  },

  logout() {
    return http.post('/logout')
  },

  me() {
    return http.get<User>('/user')
  },
}
