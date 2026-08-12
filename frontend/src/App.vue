<script setup lang="ts">
</script>

<template>
  <header class="app-header">
    <RouterLink to="/" class="brand">BuildingOS</RouterLink>
    <nav>
      <RouterLink to="/parking">車輛門禁</RouterLink>
      <RouterLink to="/packages">包裹管理</RouterLink>
      <RouterLink to="/visitors">訪客通知</RouterLink>
    </nav>
  </header>
  <main>
    <RouterView />
  </main>
</template>

<style scoped>
.app-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #3336;
}
.brand {
  font-weight: 700;
  text-decoration: none;
  color: inherit;
}
nav {
  display: flex;
  gap: 1rem;
}
main {
  padding: 1.5rem;
}
</style>
