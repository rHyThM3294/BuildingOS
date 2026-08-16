import axios from 'axios'

/**
 * 統一的 axios instance。所有 API 呼叫都必須經過 services/ 這一層，
 * 元件與 composables 不直接 import axios，方便未來替換底層 HTTP 實作
 * (例如 App 版改用原生 fetch + 憑證加密儲存) 而不動到呼叫端。
 */
export const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? '/api',
  timeout: 10_000,
})

http.interceptors.request.use((config) => {
  const token = localStorage.getItem('buildingos_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

http.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // 動態 import，避免 http.ts 一載入就去拉 router/Pinia store，
      // 造成模組初始化順序的循環依賴；這兩個只有真的 401 時才需要。
      const [{ router }, { useAuthStore }] = await Promise.all([
        import('@/router'),
        import('@/stores/auth'),
      ])

      const auth = useAuthStore()
      const wasAuthenticated = auth.isAuthenticated
      auth.clearSession()

      // 只有「原本自認為已登入，卻被判 401」才導頁——避免访客瀏覽公開頁面時
      // 任何一支意外回 401 的請求就把人強制導去登入頁。也避免在登入頁本身
      // 因為 /login 端點的錯誤（實際上是 422，不會走到這裡）又導一次頁。
      if (wasAuthenticated && router.currentRoute.value.name !== 'login') {
        router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
      }
    }

    return Promise.reject(error)
  },
)
