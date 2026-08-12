import { http } from './http'

/**
 * LINE Messaging API 的推播必須帶 Channel Access Token，不能在瀏覽器端直接呼叫
 * (會暴露密鑰)，因此前端只打自家後端的轉發端點，由後端 (backend/) 依
 * LINE 官方 OpenAPI 規格 (https://github.com/line/line-openapi) 呼叫
 * https://api.line.me/v2/bot/message/push。
 */
export interface NotifyPayload {
  targetUnit: string
  message: string
}

export const notifyService = {
  sendLineMessage(payload: NotifyPayload) {
    return http.post<{ success: boolean }>('/notifications/line', payload)
  },
}
