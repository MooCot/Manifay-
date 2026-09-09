<script setup lang="ts">
defineProps<{ active: boolean, direction: 'asc' | 'desc' }>()
</script>

<template>
  <svg
    class="h-[1em] w-[1em] shrink-0"
    :class="active ? 'text-current' : 'text-gray-400'"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
  >
    <path v-if="active && direction === 'asc'" d="M8 18l4-12 4 12" />
    <path v-else-if="active && direction === 'desc'" d="M8 6l4 12 4-12" />
    <template v-else>
      <path d="M8 11l4-4 4 4" />
      <path d="M16 13l-4 4-4-4" />
    </template>
  </svg>
</template>
