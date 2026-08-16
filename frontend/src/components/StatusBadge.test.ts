import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import StatusBadge, { type StatusBadgeEntry } from './StatusBadge.vue'

const labelMap: Record<string, StatusBadgeEntry> = {
  pending: { label: '待通知', tone: 'warning' },
  collected: { label: '已領取', tone: 'success' },
}

describe('StatusBadge', () => {
  it('renders the mapped label and tone class for a known status', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'collected', labelMap } })

    expect(wrapper.text()).toContain('已領取')
    expect(wrapper.find('.badge-success').exists()).toBe(true)
  })

  it('switches label and tone class when the status prop changes', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'pending', labelMap } })

    expect(wrapper.text()).toContain('待通知')
    expect(wrapper.find('.badge-warning').exists()).toBe(true)
  })

  it('falls back to the raw status and a neutral tone when the status is not in labelMap', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'unknown', labelMap } })

    expect(wrapper.text()).toContain('unknown')
    expect(wrapper.find('.badge-neutral').exists()).toBe(true)
  })
})
