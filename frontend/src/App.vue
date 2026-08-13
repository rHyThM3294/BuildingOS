<script setup lang="ts">
import { useRoute } from 'vue-router'

const route = useRoute()

const navItems = [
  { to: '/parking', label: '車輛門禁' },
  { to: '/packages', label: '包裹管理' },
  { to: '/visitors', label: '訪客通知' },
]
</script>

<template>
  <header class="app-header">
    <div class="app-header-inner">
      <RouterLink to="/" class="brand">
        <span class="brand-mark" aria-hidden="true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 21h18" />
            <path d="M5 21V7l7-4 7 4v14" />
            <path d="M9 9h1M14 9h1M9 13h1M14 13h1M9 17h1M14 17h1" />
          </svg>
        </span>
        <span class="brand-text">BuildingOS</span>
      </RouterLink>

      <nav class="app-nav">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="app-nav-link"
          :class="{ 'is-active': route.path === item.to }"
        >
          {{ item.label }}
        </RouterLink>
      </nav>
    </div>
  </header>

  <main class="app-main">
    <RouterView />
  </main>
</template>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 20;
  background: color-mix(in srgb, var(--surface) 88%, transparent);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border);
}

.app-header-inner {
  max-width: 1080px;
  margin: 0 auto;
  padding: 0 24px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 750;
  font-size: 16px;
  letter-spacing: -0.01em;
  text-decoration: none;
  color: var(--text-heading);
  flex: none;
}

.brand-mark {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 9px;
  background: var(--accent);
  color: var(--accent-contrast);
  flex: none;
}

.app-nav {
  display: flex;
  gap: 4px;
  overflow-x: auto;
}

.app-nav-link {
  display: inline-flex;
  align-items: center;
  padding: 7px 14px;
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-weight: 600;
  color: var(--text-muted);
  text-decoration: none;
  white-space: nowrap;
  transition: background-color 0.15s, color 0.15s;
}

.app-nav-link:hover {
  color: var(--text-heading);
  background: var(--gray-100);
}

.app-nav-link.is-active {
  color: var(--accent);
  background: var(--brand-50);
}

@media (prefers-color-scheme: dark) {
  .app-nav-link:hover {
    background: var(--surface-raised);
  }
  .app-nav-link.is-active {
    background: color-mix(in srgb, var(--accent) 16%, transparent);
  }
}

.app-main {
  flex: 1;
}

@media (max-width: 480px) {
  .app-header-inner {
    padding: 0 12px;
    gap: 8px;
  }

  .brand-text {
    display: none;
  }

  .app-nav {
    gap: 2px;
  }

  .app-nav-link {
    padding: 7px 9px;
    font-size: 13px;
  }
}
</style>
