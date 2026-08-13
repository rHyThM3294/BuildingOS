import { describe, expect, it, vi } from 'vitest'
import { http } from './http'
import { parkingService } from './parking'

vi.mock('./http', () => ({
  http: { get: vi.fn(), post: vi.fn() },
}))

describe('parkingService', () => {
  it('recognize() posts plateNumber/direction to /parking/recognize', () => {
    parkingService.recognize({ plateNumber: 'ABC-1234', direction: 'in' })

    expect(http.post).toHaveBeenCalledWith('/parking/recognize', {
      plateNumber: 'ABC-1234',
      direction: 'in',
    })
  })

  it('getLogs() forwards direction as a query param', () => {
    parkingService.getLogs({ direction: 'out' })

    expect(http.get).toHaveBeenCalledWith('/parking/logs', { params: { direction: 'out' } })
  })
})
