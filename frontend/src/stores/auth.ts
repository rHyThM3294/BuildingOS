import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/auth'
import type { User } from '@/types/models'

const TOKEN_KEY = 'buildingos_token'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  function setToken(value: string) {
    token.value = value
    localStorage.setItem(TOKEN_KEY, value)
  }

  /** 401 攔截器跟真的登出都會走這裡：清乾淨本地狀態，不呼叫 API（token 可能已經失效）。 */
  function clearSession() {
    token.value = null
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
  }

  async function login(email: string, password: string) {
    const { data } = await authService.login(email, password)
    setToken(data.token)
    user.value = data.user
  }

  async function logout() {
    try {
      await authService.logout()
    } finally {
      clearSession()
    }
  }

  /** 用來驗證 localStorage 裡的 token 是否還有效，順便把 user 資料補回來（例如整頁重新整理後）。 */
  async function fetchCurrentUser() {
    const { data } = await authService.me()
    user.value = data
  }

  return { token, user, isAuthenticated, setToken, clearSession, login, logout, fetchCurrentUser }
})
