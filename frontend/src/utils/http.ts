/** 判斷一個 catch 到的錯誤是不是特定 HTTP status 的 axios error，不用整包引入 axios 的 isAxiosError。 */
export function isHttpStatus(e: unknown, status: number): boolean {
  return typeof e === 'object' && e !== null && 'response' in e && (e as { response?: { status?: number } }).response?.status === status
}
