<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useParkingLog } from '@/composables/useParkingLog'
import { usePackage } from '@/composables/usePackage'
import { useVisitor } from '@/composables/useVisitor'

const { logs, fetchLogs } = useParkingLog()
const { packages, fetchPackages } = usePackage()
const { visitors, fetchVisitors } = useVisitor()

onMounted(() => {
  fetchLogs()
  fetchPackages()
  fetchVisitors()
})

const pendingPackages = computed(() => packages.value.filter((p) => p.status !== 'collected').length)
const waitingVisitors = computed(() => visitors.value.filter((v) => v.status === 'waiting' || v.status === 'notified').length)

const modules = [
  {
    name: '車輛門禁',
    path: '/parking',
    desc: '輸入車牌號碼進行辨識，自動登記進出紀錄',
    stat: computed(() => `${logs.value.length} 筆紀錄`),
    icon: 'car',
  },
  {
    name: '包裹管理',
    path: '/packages',
    desc: '登記到貨包裹，追蹤住戶領取狀態',
    stat: computed(() => `${pendingPackages.value} 件待處理`),
    icon: 'package',
  },
  {
    name: '訪客 / 外送',
    path: '/visitors',
    desc: '登記訪客與外送到達，即時通知住戶',
    stat: computed(() => `${waitingVisitors.value} 筆處理中`),
    icon: 'bell',
  },
]
</script>

<template>
  <div class="page">
    <section class="hero">
      <span class="eyebrow">智慧大樓整合平台</span>
      <h1>BuildingOS</h1>
      <p class="hero-desc">
        整合車輛門禁、包裹管理、訪客與外送到達通知的一站式管理平台。
      </p>
    </section>

    <div class="module-grid">
      <RouterLink v-for="m in modules" :key="m.path" :to="m.path" class="module-card card">
        <div class="module-icon">
          <svg v-if="m.icon === 'car'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14M5 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm14 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM3 17V11l2-5h10l4 5v6" /><path d="M3 11h16" /></svg>
          <svg v-else-if="m.icon === 'package'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z" /><path d="M3 8v8l9 5 9-5V8" /><path d="M12 13v8" /></svg>
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9Z" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" /></svg>
        </div>
        <h2>{{ m.name }}</h2>
        <p class="module-desc">{{ m.desc }}</p>
        <div class="module-footer">
          <span class="module-stat">{{ m.stat.value }}</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="module-arrow"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </div>
      </RouterLink>
    </div>
  </div>
</template>

<style scoped>
.hero {
  padding: 8px 0 40px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.eyebrow {
  font-size: 13px;
  font-weight: 700;
  color: var(--accent);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.hero h1 {
  font-size: 36px;
}

.hero-desc {
  color: var(--text-muted);
  font-size: 15px;
  max-width: 46em;
}

.module-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.module-card {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 22px;
  text-decoration: none;
  color: inherit;
  transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
}

.module-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  border-color: var(--brand-400);
}

.module-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--brand-50);
  color: var(--accent);
}

@media (prefers-color-scheme: dark) {
  .module-icon {
    background: color-mix(in srgb, var(--accent) 16%, transparent);
  }
}

.module-desc {
  color: var(--text-muted);
  font-size: 14px;
  flex: 1;
}

.module-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 8px;
  border-top: 1px solid var(--border);
}

.module-stat {
  font-size: 13px;
  font-weight: 650;
  color: var(--text-heading);
}

.module-arrow {
  color: var(--text-muted);
  transition: transform 0.15s, color 0.15s;
}

.module-card:hover .module-arrow {
  transform: translateX(3px);
  color: var(--accent);
}
</style>
