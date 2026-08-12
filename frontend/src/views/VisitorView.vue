<script setup lang="ts">
import { onMounted } from 'vue'
import { useVisitor } from '@/composables/useVisitor'

const { visitors, loading, fetchVisitors, setStatus } = useVisitor()

onMounted(fetchVisitors)
</script>

<template>
  <section>
    <h1>訪客 / 外送到達通知</h1>
    <p v-if="loading">載入中…</p>
    <table v-else-if="visitors.length">
      <thead>
        <tr>
          <th>訪客</th>
          <th>類型</th>
          <th>拜訪戶</th>
          <th>狀態</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="v in visitors" :key="v.id">
          <td>{{ v.visitorName }}</td>
          <td>{{ v.visitorType === 'guest' ? '訪客' : '外送' }}</td>
          <td>{{ v.targetUnit }}</td>
          <td>{{ v.status }}</td>
          <td>
            <button v-if="v.status === 'waiting'" @click="setStatus(v.id, 'notified')">通知住戶</button>
            <button v-else-if="v.status === 'notified'" @click="setStatus(v.id, 'entered')">標記進入</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else>尚無訪客紀錄</p>
  </section>
</template>
