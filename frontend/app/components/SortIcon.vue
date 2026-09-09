<script setup lang="ts">
withDefaults(defineProps<{ active: boolean, direction: 'asc' | 'desc', size?: 'sm' | 'lg' }>(), {
  size: 'sm',
})
</script>

<template>
  <svg
    :class="[
      size === 'lg' ? 'h-5 w-5' : 'h-3.5 w-3.5',
      'shrink-0',
      active ? 'text-current' : 'text-gray-400',
    ]"
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
      <path d="M8 10l4-4 4 4" />
      <path d="M16 14l-4 4-4-4" />
    </template>
  </svg>
</template>
