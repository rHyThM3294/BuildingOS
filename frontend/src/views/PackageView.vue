<script setup lang="ts">
import { onMounted } from 'vue'
import { usePackage } from '@/composables/usePackage'

const { packages, loading, fetchPackages, collect } = usePackage()

onMounted(fetchPackages)
</script>

<template>
  <section>
    <h1>包裹管理</h1>
    <p v-if="loading">載入中…</p>
    <table v-else-if="packages.length">
      <thead>
        <tr>
          <th>單號</th>
          <th>收件戶</th>
          <th>收件人</th>
          <th>狀態</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in packages" :key="p.id">
          <td>{{ p.trackingNo }}</td>
          <td>{{ p.recipientUnit }}</td>
          <td>{{ p.recipientName }}</td>
          <td>{{ p.status }}</td>
          <td>
            <button v-if="p.status !== 'collected'" @click="collect(p.id)">標記已領取</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else>尚無包裹紀錄</p>
  </section>
</template>
