<script setup lang="ts">
import type { Invoice } from '~/types/invoice'

const route = useRoute()
const id = route.params.id as string

const { data, status, error, refresh } = useInvoice(id)

const justSaved = ref(false)
let justSavedTimeout: ReturnType<typeof setTimeout> | undefined

function onSaved(updatedInvoice: Invoice) {
  if (data.value) {
    data.value.data = updatedInvoice
  }
  justSaved.value = true
  clearTimeout(justSavedTimeout)
  justSavedTimeout = setTimeout(() => {
    justSaved.value = false
  }, 3000)
}
</script>

<template>
  <div class="mx-auto max-w-4xl p-4 sm:p-6">
    <NuxtLink to="/invoices" class="mb-4 inline-block text-sm text-indigo-600">← До списку</NuxtLink>

    <div v-if="status === 'pending'" class="animate-pulse space-y-4">
      <div class="flex items-center justify-between">
        <div class="h-6 w-32 rounded bg-gray-200" />
        <div class="h-5 w-20 rounded-full bg-gray-200" />
      </div>
      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 p-4">
          <div class="mb-4 h-4 w-16 rounded bg-gray-200" />
          <div class="space-y-4">
            <div v-for="i in 4" :key="i" class="space-y-1">
              <div class="h-3 w-20 rounded bg-gray-200" />
              <div class="h-4 w-32 rounded bg-gray-200" />
            </div>
          </div>
        </div>
        <div class="rounded-lg border border-gray-200 p-4">
          <div class="mb-3 h-4 w-24 rounded bg-gray-200" />
          <div class="space-y-3">
            <div class="h-9 rounded bg-gray-200" />
            <div class="h-9 rounded bg-gray-200" />
            <div class="h-9 w-24 rounded bg-gray-200" />
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="rounded border border-red-200 bg-red-50 p-4 text-red-700">
      <template v-if="error.statusCode === 404">Інвойс не знайдено.</template>
      <template v-else>
        Не вдалося завантажити інвойс.
        <button class="underline" @click="refresh()">Спробувати ще раз</button>
      </template>
    </div>

    <div v-else-if="data?.data" class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
          <h1 class="text-xl font-semibold break-all">{{ data.data.number }}</h1>
          <Transition name="fade">
            <span v-if="justSaved" role="status" class="text-sm font-medium text-green-600">✓ Збережено</span>
          </Transition>
        </div>
        <InvoiceStatusBadge :status="data.data.status" />
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 p-4">
          <h2 class="mb-4 text-sm font-semibold text-gray-700">Деталі</h2>
          <div class="space-y-4 text-sm">
            <div>
              <h3 class="mb-1 text-xs font-medium tracking-wide text-gray-400 uppercase">Постачальник</h3>
              <p>{{ data.data.supplier_name }}</p>
              <p class="text-gray-500">ІПН {{ data.data.supplier_tax_id }}</p>
            </div>

            <div>
              <h3 class="mb-1 text-xs font-medium tracking-wide text-gray-400 uppercase">Терміни</h3>
              <p>Видано: {{ formatDate(data.data.issue_date) }}</p>
              <p>Оплата до: {{ formatDate(data.data.due_date) }}</p>
            </div>

            <div>
              <h3 class="mb-1 text-xs font-medium tracking-wide text-gray-400 uppercase">Сума</h3>
              <p class="text-base font-medium">{{ data.data.gross_amount }} {{ data.data.currency }}</p>
            </div>

            <div>
              <h3 class="mb-1 text-xs font-medium tracking-wide text-gray-400 uppercase">Оновлено</h3>
              <p class="text-gray-500">{{ new Date(data.data.updated_at).toLocaleString('uk-UA') }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-gray-200 p-4">
          <h2 class="mb-3 text-sm font-semibold text-gray-700">Редагування</h2>
          <InvoiceEditForm :invoice="data.data" :just-saved="justSaved" @saved="onSaved" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
