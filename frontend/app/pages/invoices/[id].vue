<script setup lang="ts">
const route = useRoute()
const id = route.params.id as string

const { data, status, error, refresh } = await useInvoice(id)

function onSaved() {
  refresh()
}
</script>

<template>
  <div class="mx-auto max-w-2xl p-6">
    <NuxtLink to="/invoices" class="mb-4 inline-block text-sm text-indigo-600">← До списку</NuxtLink>

    <div v-if="status === 'pending'" class="animate-pulse space-y-4">
      <div class="flex items-center justify-between">
        <div class="space-y-2">
          <div class="h-6 w-32 rounded bg-gray-200" />
          <div class="h-4 w-48 rounded bg-gray-200" />
        </div>
        <div class="h-5 w-20 rounded-full bg-gray-200" />
      </div>
      <div class="h-4 w-16 rounded bg-gray-200" />
      <div class="rounded-lg border border-gray-200 p-4">
        <div class="mb-3 h-4 w-24 rounded bg-gray-200" />
        <div class="space-y-3">
          <div class="h-9 rounded bg-gray-200" />
          <div class="h-9 rounded bg-gray-200" />
          <div class="h-9 w-24 rounded bg-gray-200" />
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
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold">{{ data.data.number }}</h1>
          <p class="text-sm text-gray-500">{{ data.data.supplier_name }} · Оплата до {{ data.data.due_date }}</p>
        </div>
        <InvoiceStatusBadge :status="data.data.status" />
      </div>

      <!-- решта полів — за клікабельним summary, не привертають уваги,
           поки не потрібні -->
      <details class="text-sm text-gray-500">
        <summary class="cursor-pointer select-none">Деталі</summary>
        <div class="mt-2 space-y-1 pl-4">
          <p>ІПН постачальника: {{ data.data.supplier_tax_id }}</p>
          <p>Дата видачі: {{ data.data.issue_date }}</p>
          <p>Сума брутто: {{ data.data.gross_amount }} {{ data.data.currency }}</p>
          <p>Оновлено: {{ new Date(data.data.updated_at).toLocaleString('uk-UA') }}</p>
        </div>
      </details>

      <div class="rounded-lg border border-gray-200 p-4">
        <h2 class="mb-3 text-sm font-semibold text-gray-700">Редагування</h2>
        <InvoiceEditForm :invoice="data.data" @saved="onSaved" />
      </div>
    </div>
  </div>
</template>
