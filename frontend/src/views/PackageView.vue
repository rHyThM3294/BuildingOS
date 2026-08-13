<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { usePackage } from '@/composables/usePackage'

const { packages, loading, fetchPackages, notify, collect, register } = usePackage()

const form = reactive({ trackingNo: '', recipientUnit: '', recipientName: '', courier: '' })
const submitting = ref(false)
const actionError = ref<string | null>(null)
const actingId = ref<number | null>(null)

async function onNotify(id: number) {
  actionError.value = null
  actingId.value = id
  try {
    await notify(id)
  } catch {
    actionError.value = '通知失敗，請稍後再試'
  } finally {
    actingId.value = null
  }
}

async function onCollect(id: number) {
  actionError.value = null
  actingId.value = id
  try {
    await collect(id)
  } catch {
    actionError.value = '更新失敗，請稍後再試'
  } finally {
    actingId.value = null
  }
}

async function onSubmit() {
  if (!form.trackingNo || !form.recipientUnit || !form.recipientName) return
  submitting.value = true
  try {
    await register({ ...form, courier: form.courier || null })
    form.trackingNo = ''
    form.recipientUnit = ''
    form.recipientName = ''
    form.courier = ''
  } finally {
    submitting.value = false
  }
}

function formatTime(iso: string) {
  return new Date(iso).toLocaleString('zh-TW', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const statusLabel: Record<string, string> = { pending: '待通知', notified: '待領取', collected: '已領取' }

onMounted(fetchPackages)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1>包裹管理</h1>
        <p>登記到貨包裹，追蹤住戶領取狀態</p>
      </div>
    </div>

    <div class="card card-pad">
      <form class="pkg-form" @submit.prevent="onSubmit">
        <div class="field" style="flex: 1 1 160px">
          <label for="trackingNo">包裹單號</label>
          <input id="trackingNo" v-model="form.trackingNo" class="input" placeholder="SF1234567890" autocomplete="off" />
        </div>
        <div class="field" style="flex: 1 1 120px">
          <label for="unit">收件戶</label>
          <input id="unit" v-model="form.recipientUnit" class="input" placeholder="A-1203" autocomplete="off" />
        </div>
        <div class="field" style="flex: 1 1 120px">
          <label for="name">收件人</label>
          <input id="name" v-model="form.recipientName" class="input" placeholder="王小明" autocomplete="off" />
        </div>
        <div class="field" style="flex: 1 1 140px">
          <label for="courier">貨運公司</label>
          <input id="courier" v-model="form.courier" class="input" placeholder="黑貓宅急便（選填）" autocomplete="off" />
        </div>
        <button class="btn btn-primary" :disabled="submitting || !form.trackingNo || !form.recipientUnit || !form.recipientName" type="submit">
          <span v-if="submitting" class="spinner" aria-hidden="true"></span>
          登記包裹
        </button>
      </form>
    </div>

    <p v-if="actionError" class="alert alert-danger" style="margin-top: 16px">{{ actionError }}</p>

    <div class="card" style="margin-top: 20px">
      <div v-if="loading && !packages.length" class="state-block">
        <span class="spinner" aria-hidden="true"></span>
        <span>載入中…</span>
      </div>

      <div v-else-if="!packages.length" class="state-block">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z" /><path d="M3 8v8l9 5 9-5V8" /><path d="M12 13v8" /></svg>
        <strong>尚無包裹紀錄</strong>
        <span>在上方登記第一筆包裹試試看</span>
      </div>

      <div v-else class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>單號</th>
              <th>收件戶</th>
              <th>收件人</th>
              <th>貨運</th>
              <th>狀態</th>
              <th>到貨時間</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in packages" :key="p.id">
              <td><strong>{{ p.trackingNo }}</strong></td>
              <td>{{ p.recipientUnit }}</td>
              <td>{{ p.recipientName }}</td>
              <td class="cell-muted">{{ p.courier ?? '—' }}</td>
              <td>
                <span v-if="p.status === 'collected'" class="badge badge-success"><span class="badge-dot" />{{ statusLabel[p.status] }}</span>
                <span v-else-if="p.status === 'notified'" class="badge badge-info"><span class="badge-dot" />{{ statusLabel[p.status] }}</span>
                <span v-else class="badge badge-warning"><span class="badge-dot" />{{ statusLabel[p.status] }}</span>
              </td>
              <td class="cell-muted">{{ formatTime(p.arrivedAt) }}</td>
              <td>
                <button
                  v-if="p.status === 'pending'"
                  class="btn btn-ghost btn-sm"
                  :disabled="actingId === p.id"
                  @click="onNotify(p.id)"
                >
                  <span v-if="actingId === p.id" class="spinner" aria-hidden="true"></span>
                  通知住戶
                </button>
                <button
                  v-else-if="p.status === 'notified'"
                  class="btn btn-ghost btn-sm"
                  :disabled="actingId === p.id"
                  @click="onCollect(p.id)"
                >
                  <span v-if="actingId === p.id" class="spinner" aria-hidden="true"></span>
                  標記已領取
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pkg-form {
  display: flex;
  gap: 14px;
  align-items: flex-end;
  flex-wrap: wrap;
}
</style>
