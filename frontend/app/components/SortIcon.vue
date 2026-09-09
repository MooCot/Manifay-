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
    <path v-if="active && direction === 'asc'" d="M6 17l6-10 6 10" />
    <path v-else-if="active && direction === 'desc'" d="M6 7l6 10 6-10" />
    <template v-else>
      <path d="M9 8l3-4 3 4" />
      <path d="M9 16l3 4 3-4" />
    </template>
  </svg>
</template>
