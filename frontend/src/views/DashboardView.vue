<script setup lang="ts">
const modules = [
  { name: '車輛門禁', path: '/parking', desc: '車牌辨識、進出紀錄' },
  { name: '包裹管理', path: '/packages', desc: '包裹到貨登記與領取狀態' },
  { name: '訪客 / 外送', path: '/visitors', desc: '登記、通知住戶、狀態流轉' },
]
</script>

<template>
  <div class="dashboard">
    <h1>BuildingOS 智慧大樓整合平台</h1>
    <div class="cards">
      <RouterLink v-for="m in modules" :key="m.path" :to="m.path" class="card">
        <h2>{{ m.name }}</h2>
        <p>{{ m.desc }}</p>
      </RouterLink>
    </div>
  </div>
</template>

<style scoped>
.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1.5rem;
}
.card {
  display: block;
  padding: 1.25rem;
  border: 1px solid #3336;
  border-radius: 8px;
  text-decoration: none;
  color: inherit;
}
</style>
