<script setup lang="ts">
import type { InvoiceStatus } from '~/types/invoice'

defineProps<{ status: InvoiceStatus }>()

const styles: Record<InvoiceStatus, string> = {
  pending: 'bg-amber-100 text-amber-800',
  approved: 'bg-emerald-100 text-emerald-800',
  rejected: 'bg-red-100 text-red-800',
}

const labels: Record<InvoiceStatus, string> = {
  pending: 'Очікує',
  approved: 'Затверджено',
  rejected: 'Відхилено',
}
</script>

<template>
  <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', styles[status]]">
    {{ labels[status] }}
  </span>
</template>
