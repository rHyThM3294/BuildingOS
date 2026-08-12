/**
 * 平台抽象層：即時通知介面。
 *
 * Web 版先用瀏覽器 Notification API 示意「住戶收到到達通知」。
 * 未來 App 化時，改實作 push.capacitor.ts 呼叫 @capacitor/push-notifications
 * 註冊 FCM/APNs token 並回傳給後端，呼叫端一樣透過 PushPort 介面操作。
 */
export interface PushPort {
  requestPermission(): Promise<boolean>
  show(title: string, body: string): void
}

class WebPush implements PushPort {
  async requestPermission(): Promise<boolean> {
    if (!('Notification' in window)) return false
    const result = await Notification.requestPermission()
    return result === 'granted'
  }

  show(title: string, body: string): void {
    if (!('Notification' in window) || Notification.permission !== 'granted') return
    new Notification(title, { body })
  }
}

// 未來 App 化：改成依 Capacitor.isNativePlatform() 回傳 CapacitorPush 實例
export const push: PushPort = new WebPush()
