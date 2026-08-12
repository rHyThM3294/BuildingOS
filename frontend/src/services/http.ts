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
  (error) => {
    // TODO: 統一錯誤處理 (401 導回登入頁、跳 toast 等)
    return Promise.reject(error)
  },
)
