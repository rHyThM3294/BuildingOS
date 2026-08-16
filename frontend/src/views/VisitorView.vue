<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useVisitor } from '@/composables/useVisitor'
import StatusBadge, { type StatusBadgeEntry } from '@/components/StatusBadge.vue'
import type { VisitorType } from '@/types/models'

const { visitors, loading, fetchVisitors, setStatus, register } = useVisitor()

const form = reactive<{ visitorName: string; visitorType: VisitorType; targetUnit: string }>({
  visitorName: '',
  visitorType: 'guest',
  targetUnit: '',
})
const submitting = ref(false)

async function onSubmit() {
  if (!form.visitorName || !form.targetUnit) return
  submitting.value = true
  try {
    await register({ ...form })
    form.visitorName = ''
    form.targetUnit = ''
  } finally {
    submitting.value = false
  }
}

function formatTime(iso: string) {
  return new Date(iso).toLocaleString('zh-TW', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const statusLabel: Record<string, StatusBadgeEntry> = {
  waiting: { label: '等待中', tone: 'warning' },
  notified: { label: '已通知', tone: 'info' },
  entered: { label: '已進入', tone: 'success' },
  left: { label: '已離開', tone: 'success' },
}

onMounted(fetchVisitors)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1>訪客 / 外送到達通知</h1>
        <p>登記訪客或外送到達，通知住戶並追蹤狀態流轉</p>
      </div>
    </div>

    <div class="card card-pad">
      <form class="visitor-form" @submit.prevent="onSubmit">
        <div class="field" style="flex: 1 1 160px">
          <label for="visitorName">訪客姓名</label>
          <input id="visitorName" v-model="form.visitorName" class="input" placeholder="王小明 / foodpanda 外送員" autocomplete="off" />
        </div>
        <div class="field" style="flex: 1 1 120px">
          <label for="visitorType">類型</label>
          <select id="visitorType" v-model="form.visitorType" class="input">
            <option value="guest">訪客</option>
            <option value="delivery">外送</option>
          </select>
        </div>
        <div class="field" style="flex: 1 1 120px">
          <label for="targetUnit">拜訪戶</label>
          <input id="targetUnit" v-model="form.targetUnit" class="input" placeholder="A-1203" autocomplete="off" />
        </div>
        <button class="btn btn-primary" :disabled="submitting || !form.visitorName || !form.targetUnit" type="submit">
          <span v-if="submitting" class="spinner" aria-hidden="true"></span>
          登記到達
        </button>
      </form>
    </div>

    <div class="card" style="margin-top: 20px">
      <div v-if="loading && !visitors.length" class="state-block">
        <span class="spinner" aria-hidden="true"></span>
        <span>載入中…</span>
      </div>

      <div v-else-if="!visitors.length" class="state-block">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9Z" /></svg>
        <strong>尚無訪客紀錄</strong>
        <span>在上方登記第一筆到達通知試試看</span>
      </div>

      <div v-else class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>訪客</th>
              <th>類型</th>
              <th>拜訪戶</th>
              <th>狀態</th>
              <th>登記時間</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="v in visitors" :key="v.id">
              <td><strong>{{ v.visitorName }}</strong></td>
              <td>
                <span class="badge badge-neutral">{{ v.visitorType === 'guest' ? '訪客' : '外送' }}</span>
              </td>
              <td>{{ v.targetUnit }}</td>
              <td>
                <StatusBadge :status="v.status" :label-map="statusLabel" />
              </td>
              <td class="cell-muted">{{ formatTime(v.registeredAt) }}</td>
              <td>
                <button v-if="v.status === 'waiting'" class="btn btn-ghost btn-sm" @click="setStatus(v.id, 'notified')">通知住戶</button>
                <button v-else-if="v.status === 'notified'" class="btn btn-ghost btn-sm" @click="setStatus(v.id, 'entered')">標記進入</button>
                <button v-else-if="v.status === 'entered'" class="btn btn-ghost btn-sm" @click="setStatus(v.id, 'left')">標記離開</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.visitor-form {
  display: flex;
  gap: 14px;
  align-items: flex-end;
  flex-wrap: wrap;
}
</style>
