<script setup lang="ts">
const { data, status, error, refresh } = await useInvoices()
</script>

<template>
  <div class="mx-auto max-w-5xl p-6">
    <h1 class="mb-4 text-xl font-semibold">Інвойси</h1>

    <div v-if="status === 'pending'" class="text-gray-500">Завантаження…</div>

    <div v-else-if="error" class="rounded border border-red-200 bg-red-50 p-4 text-red-700">
      Не вдалося завантажити список інвойсів.
      <button class="ml-2 underline" @click="refresh()">Спробувати ще раз</button>
    </div>

    <div v-else-if="!data?.data?.length" class="text-gray-500">Інвойсів ще немає.</div>

    <table v-else class="w-full border-collapse text-left text-sm">
      <thead>
        <tr class="border-b text-gray-500">
          <th class="py-2">Номер</th>
          <th class="py-2">Постачальник</th>
          <th class="py-2">Сума (брутто)</th>
          <th class="py-2">Статус</th>
          <th class="py-2">Термін оплати</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="invoice in data.data"
          :key="invoice.id"
          class="cursor-pointer border-b hover:bg-gray-50"
          @click="navigateTo(`/invoices/${invoice.id}`)"
        >
          <td class="max-w-[200px] truncate py-2" :title="invoice.number">{{ invoice.number }}</td>
          <td class="max-w-[220px] truncate py-2" :title="invoice.supplier_name">{{ invoice.supplier_name }}</td>
          <td class="py-2">{{ invoice.gross_amount }} {{ invoice.currency }}</td>
          <td class="py-2"><InvoiceStatusBadge :status="invoice.status" /></td>
          <td class="py-2">{{ invoice.due_date }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
