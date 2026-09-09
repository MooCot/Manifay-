<script setup lang="ts">
const route = useRoute()
const router = useRouter()

const page = computed({
  get: () => Number(route.query.page) || 1,
  set: (value) => router.push({ query: { ...route.query, page: String(value) } }),
})

const { data, status, error, refresh } = await useInvoices(page)
</script>

<template>
  <div class="mx-auto flex h-dvh max-w-5xl flex-col p-6">
    <h1 class="mb-4 shrink-0 text-xl font-semibold">Інвойси</h1>

    <div v-if="status === 'pending'" class="text-gray-500">Завантаження…</div>

    <div v-else-if="error" class="rounded border border-red-200 bg-red-50 p-4 text-red-700">
      Не вдалося завантажити список інвойсів.
      <button class="ml-2 underline" @click="refresh()">Спробувати ще раз</button>
    </div>

    <div v-else-if="!data?.data?.length" class="text-gray-500">Інвойсів ще немає.</div>

    <template v-else>
      <!-- min-h-0 обов'язковий для flex-дитини з overflow — інакше вона не
           стискається і скрол не спрацьовує (класична flexbox-пастка) -->
      <div class="thin-scrollbar min-h-0 flex-1 overflow-y-auto">
        <table class="w-full border-collapse text-left text-sm">
          <thead class="sticky top-0 bg-white">
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

      <div v-if="data.meta.last_page > 1" class="mt-4 flex shrink-0 items-center justify-between text-sm">
        <button
          class="rounded border px-3 py-1 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="data.meta.current_page <= 1"
          @click="page = data.meta.current_page - 1"
        >
          ← Попередня
        </button>
        <span class="text-gray-500">
          Сторінка {{ data.meta.current_page }} з {{ data.meta.last_page }} ({{ data.meta.total }} інвойсів)
        </span>
        <button
          class="rounded border px-3 py-1 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="data.meta.current_page >= data.meta.last_page"
          @click="page = data.meta.current_page + 1"
        >
          Наступна →
        </button>
      </div>
    </template>
  </div>
</template>
