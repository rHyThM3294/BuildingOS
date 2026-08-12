import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

/**
 * 全域使用者狀態 (管理員 / 住戶)。實際登入 API 待補，
 * 先建立 store 架構讓其他模組可以 import 使用。
 */
export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('buildingos_token'))
  const role = ref<'admin' | 'resident' | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  function setToken(value: string) {
    token.value = value
    localStorage.setItem('buildingos_token', value)
  }

  function logout() {
    token.value = null
    role.value = null
    localStorage.removeItem('buildingos_token')
  }

  return { token, role, isAuthenticated, setToken, logout }
})
