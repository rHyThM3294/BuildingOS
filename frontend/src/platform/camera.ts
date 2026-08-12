/**
 * 平台抽象層：拍照/選圖介面。
 *
 * Web 版現在用 <input type="file" capture> 模擬「拍攝車牌照片」。
 * 未來若用 Capacitor 包裝成 App，只要新增 camera.capacitor.ts 實作同一個
 * CameraPort 介面 (改呼叫 @capacitor/camera)，並在此檔案依執行環境切換，
 * 呼叫端 (composables/views)完全不用修改。
 */
export interface CameraPort {
  /** 回傳圖片的 base64 dataURL，使用者取消則回傳 null */
  capturePhoto(): Promise<string | null>
}

class WebCamera implements CameraPort {
  capturePhoto(): Promise<string | null> {
    return new Promise((resolve) => {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'image/*'
      input.capture = 'environment'
      input.onchange = () => {
        const file = input.files?.[0]
        if (!file) return resolve(null)
        const reader = new FileReader()
        reader.onload = () => resolve(reader.result as string)
        reader.readAsDataURL(file)
      }
      input.click()
    })
  }
}

// 未來 App 化：改成依 Capacitor.isNativePlatform() 回傳 CapacitorCamera 實例
export const camera: CameraPort = new WebCamera()
