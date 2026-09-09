<script setup lang="ts">
defineProps<{ active: boolean, direction: 'asc' | 'desc' }>()
</script>

<template>
  <svg
    class="h-3.5 w-3.5 shrink-0"
    :class="active ? 'text-current' : 'text-gray-400'"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
  >
    <path v-if="active && direction === 'asc'" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
    <path v-else-if="active && direction === 'desc'" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
    <template v-else>
      <path d="M8 9l4-4 4 4" />
      <path d="M16 15l-4 4-4-4" />
    </template>
  </svg>
</template>
