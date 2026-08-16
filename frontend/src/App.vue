<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const auth = useAuthStore()

const navItems = [
  { to: '/parking', label: '車輛門禁' },
  { to: '/packages', label: '包裹管理' },
  { to: '/visitors', label: '訪客通知' },
]

const menuOpen = ref(false)

watch(
  () => route.path,
  () => {
    menuOpen.value = false
  },
)

onMounted(() => {
  // token 可能是舊的/已被後端撤銷；驗證一次順便把使用者名稱補回來。
  // 驗證失敗的話 401 攔截器會自動清掉 session、導去登入頁。
  if (auth.isAuthenticated) {
    auth.fetchCurrentUser().catch(() => {})
  }
})
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

      <div class="account">
        <template v-if="auth.isAuthenticated">
          <span class="account-name">{{ auth.user?.name ?? '已登入' }}</span>
          <button type="button" class="btn btn-ghost btn-sm" @click="auth.logout()">登出</button>
        </template>
        <RouterLink v-else to="/login" class="btn btn-ghost btn-sm">登入</RouterLink>
      </div>

      <button
        type="button"
        class="menu-toggle"
        :aria-expanded="menuOpen"
        aria-controls="app-mobile-nav"
        :aria-label="menuOpen ? '關閉選單' : '開啟選單'"
        @click="menuOpen = !menuOpen"
      >
        <svg v-if="!menuOpen" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 7h16M4 12h16M4 17h16" />
        </svg>
        <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 6l12 12M18 6 6 18" />
        </svg>
      </button>
    </div>

    <nav v-show="menuOpen" id="app-mobile-nav" class="mobile-nav">
      <RouterLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        class="mobile-nav-link"
        :class="{ 'is-active': route.path === item.to }"
      >
        {{ item.label }}
      </RouterLink>

      <div class="mobile-account">
        <template v-if="auth.isAuthenticated">
          <span class="account-name">{{ auth.user?.name ?? '已登入' }}</span>
          <button type="button" class="btn btn-ghost btn-sm" @click="auth.logout()">登出</button>
        </template>
        <RouterLink v-else to="/login" class="btn btn-ghost btn-sm">登入</RouterLink>
      </div>
    </nav>
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
  .app-nav-link:hover,
  .mobile-nav-link:hover {
    background: var(--surface-raised);
  }
  .app-nav-link.is-active,
  .mobile-nav-link.is-active {
    background: color-mix(in srgb, var(--accent) 16%, transparent);
  }
}

.account {
  display: flex;
  align-items: center;
  gap: 10px;
}

.account-name {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--text-muted);
}

.mobile-account {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 11px 12px;
  margin-top: 4px;
  border-top: 1px solid var(--border);
}

.menu-toggle {
  display: none;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-heading);
  cursor: pointer;
  flex: none;
}

.menu-toggle:hover {
  background: var(--gray-100);
}

@media (prefers-color-scheme: dark) {
  .menu-toggle:hover {
    background: var(--surface-raised);
  }
}

.menu-toggle:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.mobile-nav {
  display: none;
  flex-direction: column;
  gap: 2px;
  padding: 8px 16px 14px;
  border-top: 1px solid var(--border);
}

.mobile-nav-link {
  padding: 11px 12px;
  border-radius: var(--radius-sm);
  font-size: 15px;
  font-weight: 600;
  color: var(--text-muted);
  text-decoration: none;
  transition: background-color 0.15s, color 0.15s;
}

.mobile-nav-link:hover {
  color: var(--text-heading);
  background: var(--gray-100);
}

.mobile-nav-link.is-active {
  color: var(--accent);
  background: var(--brand-50);
}

.app-main {
  flex: 1;
}

@media (max-width: 640px) {
  .app-header-inner {
    padding: 0 16px;
  }

  .app-nav,
  .account {
    display: none;
  }

  .menu-toggle {
    display: inline-flex;
  }

  .mobile-nav {
    display: flex;
  }
}
</style>
