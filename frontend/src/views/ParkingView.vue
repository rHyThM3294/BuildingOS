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

onMounted(fetchLogs)
</script>

<template>
  <section>
    <h1>車輛門禁管理</h1>

    <form class="plate-form" @submit.prevent="onSubmit">
      <input v-model="form.plateNumber" placeholder="輸入車牌號碼，如 ABC-1234" />
      <select v-model="form.direction">
        <option value="in">入場</option>
        <option value="out">出場</option>
      </select>
      <button :disabled="loading" type="submit">辨識並登記</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <table v-if="logs.length">
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
          <td>{{ log.plateNumber }}</td>
          <td>{{ log.direction === 'in' ? '入場' : '出場' }}</td>
          <td>{{ log.status === 'success' ? '成功' : '失敗' }}</td>
          <td>{{ log.ownerName ?? '-' }}</td>
          <td>{{ log.recognizedAt }}</td>
        </tr>
      </tbody>
    </table>
    <p v-else-if="!loading">尚無進出紀錄</p>
  </section>
</template>

<style scoped>
.plate-form {
  display: flex;
  gap: 0.5rem;
  margin: 1rem 0;
}
.error {
  color: #e5484d;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  text-align: left;
  padding: 0.5rem;
  border-bottom: 1px solid #3336;
}
</style>
