<script setup lang="ts">
import type { Invoice } from '~/types/invoice'

const route = useRoute()
const router = useRouter()

const page = computed({
  get: () => Number(route.query.page) || 1,
  set: (value) => router.push({ query: { ...route.query, page: String(value) } }),
})

const sort = computed(() => (typeof route.query.sort === 'string' ? route.query.sort : 'created_at'))
const direction = computed<'asc' | 'desc'>(() => (route.query.direction === 'asc' ? 'asc' : 'desc'))

function setSort(column: string, dir: 'asc' | 'desc') {
  router.push({ query: { ...route.query, sort: column, direction: dir, page: '1' } })
}

function toggleSort(column: string) {
  setSort(column, sort.value === column && direction.value === 'desc' ? 'asc' : 'desc')
}


const { data, status, error, refresh } = await useInvoices(page, sort, direction)

const isLoading = computed(() => status.value === 'pending')
const isEmpty = computed(() => !isLoading.value && !error.value && !data.value?.data?.length)

// на мобільному в рядку видно лише номер+статус (sm:table-cell ховає решту) —
// клік розгортає приховані поля inline, а не одразу веде на іншу сторінку
// (double-tap-навігація — антипатерн: конфліктує з нативним zoom-жестом,
// нульова discoverability). На sm+ усі поля вже видно, тому клік одразу
// переходить на деталі, як і раніше
const expandedId = ref<number | null>(null)

function isDesktopViewport(): boolean {
  return typeof window !== 'undefined' && window.matchMedia('(min-width: 640px)').matches
}

function onRowActivate(invoice: Invoice) {
  if (isDesktopViewport()) {
    navigateTo(`/invoices/${invoice.id}`)
  } else {
    expandedId.value = expandedId.value === invoice.id ? null : invoice.id
  }
}
</script>

<template>
  <div class="mx-auto flex h-dvh max-w-5xl flex-col p-4 sm:p-6">
    <div class="mb-4 flex shrink-0 flex-wrap items-center justify-between gap-2">
      <h1 class="text-xl font-semibold">Інвойси</h1>
      <!-- на мобільному колонка "Термін оплати" (а з нею й клікабельний
           заголовок) прихована sm:table-cell — той самий toggleSort, окрема
           кнопка, завжди видима незалежно від того, які колонки показані -->
      <button
        type="button"
        class="inline-flex items-center gap-1 rounded border px-2 py-1 text-sm sm:hidden"
        @click="toggleSort('due_date')"
      >
        Термін оплати
        <span v-if="sort === 'due_date'" aria-hidden="true">{{ direction === 'asc' ? '▲' : '▼' }}</span>
      </button>
    </div>

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
              <th class="hidden py-2 sm:table-cell">
                <button
                  type="button"
                  class="inline-flex items-center gap-1 hover:text-gray-700"
                  @click="toggleSort('due_date')"
                >
                  Термін оплати
                  <span v-if="sort === 'due_date'" aria-hidden="true">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </button>
              </th>
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
              <template v-for="invoice in data?.data" :key="invoice.id">
                <tr
                  tabindex="0"
                  role="button"
                  :aria-expanded="expandedId === invoice.id"
                  :aria-label="`Інвойс ${invoice.number}`"
                  class="cursor-pointer border-b hover:bg-gray-50 focus:bg-gray-50 focus:outline-2 focus:outline-indigo-500 focus:-outline-offset-2"
                  @click="onRowActivate(invoice)"
                  @keydown.enter="onRowActivate(invoice)"
                  @keydown.space.prevent="onRowActivate(invoice)"
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
                <tr v-if="expandedId === invoice.id" class="border-b bg-gray-50 sm:hidden">
                  <td colspan="5" class="px-2 py-3 text-sm">
                    <dl class="space-y-1">
                      <div class="flex justify-between">
                        <dt class="text-gray-500">Постачальник</dt>
                        <dd>{{ invoice.supplier_name }}</dd>
                      </div>
                      <div class="flex justify-between">
                        <dt class="text-gray-500">Сума</dt>
                        <dd>{{ invoice.gross_amount }} {{ invoice.currency }}</dd>
                      </div>
                      <div class="flex justify-between">
                        <dt class="text-gray-500">Термін оплати</dt>
                        <dd>{{ invoice.due_date }}</dd>
                      </div>
                    </dl>
                    <NuxtLink :to="`/invoices/${invoice.id}`" class="mt-2 inline-block text-indigo-600 underline">
                      Переглянути →
                    </NuxtLink>
                  </td>
                </tr>
              </template>
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
