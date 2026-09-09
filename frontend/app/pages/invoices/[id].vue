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

    <div v-if="status === 'pending'" class="text-gray-500">Завантаження…</div>

    <div v-else-if="error" class="rounded border border-red-200 bg-red-50 p-4 text-red-700">
      <template v-if="error.statusCode === 404">Інвойс не знайдено.</template>
      <template v-else>
        Не вдалося завантажити інвойс.
        <button class="underline" @click="refresh()">Спробувати ще раз</button>
      </template>
    </div>

    <div v-else-if="data?.data" class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ data.data.number }}</h1>
        <InvoiceStatusBadge :status="data.data.status" />
      </div>

      <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
        <dt class="text-gray-500">Постачальник</dt>
        <dd>{{ data.data.supplier_name }}</dd>
        <dt class="text-gray-500">ІПН постачальника</dt>
        <dd>{{ data.data.supplier_tax_id }}</dd>
        <dt class="text-gray-500">Дата видачі</dt>
        <dd>{{ data.data.issue_date }}</dd>
        <dt class="text-gray-500">Термін оплати</dt>
        <dd>{{ data.data.due_date }}</dd>
        <dt class="text-gray-500">Сума брутто</dt>
        <dd>{{ data.data.gross_amount }} {{ data.data.currency }}</dd>
        <dt class="text-gray-500">Востаннє оновлено</dt>
        <dd>{{ new Date(data.data.updated_at).toLocaleString('uk-UA') }}</dd>
      </dl>

      <InvoiceEditForm :invoice="data.data" @saved="onSaved" />
    </div>
  </div>
</template>
