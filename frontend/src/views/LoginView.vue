<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const form = reactive({ email: 'demo@buildingos.test', password: 'buildingos-demo' })
const loading = ref(false)
const error = ref<string | null>(null)

async function onSubmit() {
  loading.value = true
  error.value = null
  try {
    await auth.login(form.email, form.password)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    router.push(redirect)
  } catch {
    error.value = '帳號或密碼錯誤'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page login-page">
    <div class="card card-pad login-card">
      <h1>登入 BuildingOS</h1>
      <p class="login-sub">用來展示 401 攔截器的最小登入流程（Sanctum Personal Access Token）</p>

      <form class="login-form" @submit.prevent="onSubmit">
        <div class="field">
          <label for="email">Email</label>
          <input id="email" v-model="form.email" class="input" type="email" autocomplete="username" />
        </div>
        <div class="field">
          <label for="password">密碼</label>
          <input id="password" v-model="form.password" class="input" type="password" autocomplete="current-password" />
        </div>

        <p v-if="error" class="alert alert-danger">{{ error }}</p>

        <button class="btn btn-primary" :disabled="loading" type="submit">
          <span v-if="loading" class="spinner" aria-hidden="true"></span>
          登入
        </button>
      </form>

      <p class="login-hint">Demo 帳號已預先填好：<code>demo@buildingos.test</code> / <code>buildingos-demo</code></p>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  padding-top: 64px;
}

.login-card {
  width: 100%;
  max-width: 380px;
}

.login-sub {
  color: var(--text-muted);
  font-size: 13.5px;
  margin: 4px 0 20px;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.login-hint {
  margin-top: 18px;
  font-size: 12.5px;
  color: var(--text-muted);
  text-align: center;
}

.login-hint code {
  background: var(--gray-100);
  padding: 1px 5px;
  border-radius: 4px;
}

@media (prefers-color-scheme: dark) {
  .login-hint code {
    background: var(--surface-raised);
  }
}
</style>
