<script setup lang="ts">
import { onMounted, reactive } from 'vue'
import { useParkingLog } from '@/composables/useParkingLog'
import type { EntryDirection } from '@/types/models'

const { logs, loading, error, fetchLogs, recognizePlate } = useParkingLog()

const form = reactive<{ plateNumber: string; direction: EntryDirection }>({
  plateNumber: '',
  direction: 'in',
})

async function onSubmit() {
  if (!form.plateNumber) return
  await recognizePlate({ ...form })
  form.plateNumber = ''
}

function formatTime(iso: string) {
  return new Date(iso).toLocaleString('zh-TW', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

onMounted(fetchLogs)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1>車輛門禁管理</h1>
        <p>輸入車牌號碼進行辨識，系統會自動模擬辨識結果並登記進出紀錄</p>
      </div>
    </div>

    <div class="card card-pad form-card">
      <form class="plate-form" @submit.prevent="onSubmit">
        <div class="field plate-field">
          <label for="plate">車牌號碼</label>
          <input id="plate" v-model="form.plateNumber" class="input" placeholder="例如 ABC-1234" autocomplete="off" />
        </div>
        <div class="field">
          <label for="direction">方向</label>
          <select id="direction" v-model="form.direction" class="input">
            <option value="in">入場</option>
            <option value="out">出場</option>
          </select>
        </div>
        <button class="btn btn-primary" :disabled="loading || !form.plateNumber" type="submit">
          <span v-if="loading" class="spinner" aria-hidden="true"></span>
          辨識並登記
        </button>
      </form>
    </div>

    <p v-if="error" class="alert alert-danger" style="margin-top: 16px">{{ error }}</p>

    <div class="card" style="margin-top: 20px">
      <div v-if="loading && !logs.length" class="state-block">
        <span class="spinner" aria-hidden="true"></span>
        <span>載入中…</span>
      </div>

      <div v-else-if="!logs.length" class="state-block">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14M5 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm14 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM3 17V11l2-5h10l4 5v6" /></svg>
        <strong>尚無進出紀錄</strong>
        <span>在上方輸入車牌試試看</span>
      </div>

      <div v-else class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>車牌</th>
              <th>方向</th>
              <th>結果</th>
              <th>車主</th>
              <th>時間</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id">
              <td><strong>{{ log.plateNumber }}</strong></td>
              <td>
                <span class="badge badge-neutral">{{ log.direction === 'in' ? '入場' : '出場' }}</span>
              </td>
              <td>
                <span v-if="log.status === 'success'" class="badge badge-success"><span class="badge-dot" />成功</span>
                <span v-else class="badge badge-danger"><span class="badge-dot" />失敗</span>
              </td>
              <td class="cell-muted">{{ log.ownerName ?? '—' }}</td>
              <td class="cell-muted">{{ formatTime(log.recognizedAt) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.plate-form {
  display: flex;
  gap: 14px;
  align-items: flex-end;
  flex-wrap: wrap;
}

.plate-field {
  flex: 1 1 220px;
}
</style>
