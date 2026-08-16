<script setup lang="ts">
export type BadgeTone = 'success' | 'warning' | 'danger' | 'info' | 'neutral'

export interface StatusBadgeEntry {
  label: string
  tone: BadgeTone
}

const props = defineProps<{
  status: string
  labelMap: Record<string, StatusBadgeEntry>
}>()
</script>

<template>
  <span class="badge" :class="`badge-${props.labelMap[props.status]?.tone ?? 'neutral'}`">
    <span class="badge-dot" />{{ props.labelMap[props.status]?.label ?? props.status }}
  </span>
</template>
