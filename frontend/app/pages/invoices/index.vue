<script setup lang="ts">
const route = useRoute()
const router = useRouter()

const page = computed({
  get: () => Number(route.query.page) || 1,
  set: (value) => router.push({ query: { ...route.query, page: String(value) } }),
})

const { data, status, error, refresh } = await useInvoices(page)

const isLoading = computed(() => status.value === 'pending')
const isEmpty = computed(() => !isLoading.value && !error.value && !data.value?.data?.length)
</script>

<template>
  <div class="mx-auto flex h-dvh max-w-5xl flex-col p-4 sm:p-6">
    <h1 class="mb-4 shrink-0 text-xl font-semibold">Інвойси</h1>

    <div v-if="error" class="rounded border border-red-200 bg-red-50 p-4 text-red-700">
      Не вдалося завантажити список інвойсів.
      <button class="ml-2 underline" @click="refresh()">Спробувати ще раз</button>
    </div>

    <div v-else-if="isEmpty" class="text-gray-500">Інвойсів ще немає.</div>

    <template v-else>
      <!-- min-h-0 обов'язковий для flex-дитини з overflow — інакше вона не
           стискається і скрол не спрацьовує (класична flexbox-пастка).
           overflow-x-hidden — горизонтального скролу немає взагалі: на
           мобільному колонок менше (sm:table-cell ховає зайві), тому нема
           причини таблиці бути ширшою за екран -->
      <div class="thin-scrollbar min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto">
        <table class="w-full border-collapse text-left text-sm">
          <thead class="sticky top-0 bg-white">
            <tr class="border-b text-gray-500">
              <th class="py-2">Номер</th>
              <th class="hidden py-2 sm:table-cell">Постачальник</th>
              <th class="hidden py-2 sm:table-cell">Сума (брутто)</th>
              <th class="py-2">Статус</th>
              <th class="hidden py-2 sm:table-cell">Термін оплати</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="isLoading">
              <tr v-for="i in 8" :key="i" class="border-b">
                <td class="py-2"><div class="h-4 w-24 animate-pulse rounded bg-gray-200" /></td>
                <td class="hidden py-2 sm:table-cell"><div class="h-4 w-32 animate-pulse rounded bg-gray-200" /></td>
                <td class="hidden py-2 sm:table-cell"><div class="h-4 w-20 animate-pulse rounded bg-gray-200" /></td>
                <td class="py-2"><div class="h-5 w-16 animate-pulse rounded-full bg-gray-200" /></td>
                <td class="hidden py-2 sm:table-cell"><div class="h-4 w-20 animate-pulse rounded bg-gray-200" /></td>
              </tr>
            </template>
            <template v-else>
              <tr
                v-for="invoice in data?.data"
                :key="invoice.id"
                tabindex="0"
                role="link"
                :aria-label="`Переглянути інвойс ${invoice.number}`"
                class="cursor-pointer border-b hover:bg-gray-50 focus:bg-gray-50 focus:outline-2 focus:outline-indigo-500 focus:-outline-offset-2"
                @click="navigateTo(`/invoices/${invoice.id}`)"
                @keydown.enter="navigateTo(`/invoices/${invoice.id}`)"
                @keydown.space.prevent="navigateTo(`/invoices/${invoice.id}`)"
              >
                <td class="max-w-[160px] truncate py-2 sm:max-w-[200px]" :title="invoice.number">
                  {{ invoice.number }}
                </td>
                <td class="hidden max-w-[220px] truncate py-2 sm:table-cell" :title="invoice.supplier_name">
                  {{ invoice.supplier_name }}
                </td>
                <td class="hidden py-2 sm:table-cell">{{ invoice.gross_amount }} {{ invoice.currency }}</td>
                <td class="py-2"><InvoiceStatusBadge :status="invoice.status" /></td>
                <td class="hidden py-2 sm:table-cell">{{ invoice.due_date }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <div
        v-if="!isLoading && data && data.meta.last_page > 1"
        class="mt-4 flex shrink-0 flex-wrap items-center justify-between gap-2 text-sm"
      >
        <button
          class="rounded border px-3 py-1 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="data.meta.current_page <= 1"
          @click="page = data.meta.current_page - 1"
        >
          ← Попередня
        </button>
        <span class="order-first w-full text-center text-gray-500 sm:order-none sm:w-auto">
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
