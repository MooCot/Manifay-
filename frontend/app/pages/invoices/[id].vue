<script setup lang="ts">
const route = useRoute()
const id = route.params.id as string

const { data, status, error, refresh } = await useInvoice(id)

function onSaved() {
  refresh()
}
</script>

<template>
  <div class="mx-auto max-w-4xl p-6">
    <NuxtLink to="/invoices" class="mb-4 inline-block text-sm text-indigo-600">← До списку</NuxtLink>

    <div v-if="status === 'pending'" class="animate-pulse space-y-4">
      <div class="flex items-center justify-between">
        <div class="h-6 w-32 rounded bg-gray-200" />
        <div class="h-5 w-20 rounded-full bg-gray-200" />
      </div>
      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 p-4">
          <div class="mb-3 h-4 w-16 rounded bg-gray-200" />
          <div class="mx-auto w-fit space-y-3">
            <div v-for="i in 6" :key="i" class="h-4 w-40 rounded bg-gray-200" />
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
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ data.data.number }}</h1>
        <InvoiceStatusBadge :status="data.data.status" />
      </div>

      <!-- картки з рамкою — візуальне групування замість "голого" тексту в
           просторі; md:grid-cols-2 — поруч на десктопі, менше вертикального
           контенту -->
      <div class="grid gap-6 md:grid-cols-2">
        <div class="flex flex-col rounded-lg border border-gray-200 p-4">
          <h2 class="mb-3 text-sm font-semibold text-gray-700">Деталі</h2>
          <!-- справжня <table>, не grid — колонки природно підлаштовуються під
               контент (короткі дати vs довгі лейбли), а не діляться порівну
               50/50, звідки й була асиметрія. Картка вища за таблицю (тягнеться
               під висоту форми-сусіда в grid), тому центруємо таблицю у
               вільному просторі під заголовком, а не лишаємо її зверху -->
          <div class="flex flex-1 items-center justify-center">
            <table class="text-sm">
            <tbody>
              <tr>
                <td class="whitespace-nowrap py-1 pr-4 text-gray-500">Постачальник</td>
                <td class="py-1">{{ data.data.supplier_name }}</td>
              </tr>
              <tr>
                <td class="whitespace-nowrap py-1 pr-4 text-gray-500">ІПН постачальника</td>
                <td class="py-1">{{ data.data.supplier_tax_id }}</td>
              </tr>
              <tr>
                <td class="whitespace-nowrap py-1 pr-4 text-gray-500">Дата видачі</td>
                <td class="py-1">{{ data.data.issue_date }}</td>
              </tr>
              <tr>
                <td class="whitespace-nowrap py-1 pr-4 text-gray-500">Термін оплати</td>
                <td class="py-1">{{ data.data.due_date }}</td>
              </tr>
              <tr>
                <td class="whitespace-nowrap py-1 pr-4 text-gray-500">Сума брутто</td>
                <td class="py-1">{{ data.data.gross_amount }} {{ data.data.currency }}</td>
              </tr>
              <tr>
                <td class="whitespace-nowrap py-1 pr-4 text-gray-500">Востаннє оновлено</td>
                <td class="py-1">{{ new Date(data.data.updated_at).toLocaleString('uk-UA') }}</td>
              </tr>
            </tbody>
            </table>
          </div>
        </div>

        <div class="rounded-lg border border-gray-200 p-4">
          <h2 class="mb-3 text-sm font-semibold text-gray-700">Редагування</h2>
          <InvoiceEditForm :invoice="data.data" @saved="onSaved" />
        </div>
      </div>
    </div>
  </div>
</template>
