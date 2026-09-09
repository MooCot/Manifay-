<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { createInvoiceEditSchema } from '~/schemas/invoice.schema'
import type { Invoice } from '~/types/invoice'

const props = defineProps<{ invoice: Invoice }>()
const emit = defineEmits<{ saved: [Invoice] }>()

const editable = computed(() => props.invoice.status === 'pending')

const { handleSubmit, errors, defineField, isSubmitting } = useForm({
  validationSchema: toTypedSchema(createInvoiceEditSchema(props.invoice.issue_date)),
  initialValues: {
    net_amount: Number(props.invoice.net_amount),
    vat_amount: Number(props.invoice.vat_amount),
    due_date: props.invoice.due_date,
  },
})

const [netAmount, netAmountAttrs] = defineField('net_amount')
const [vatAmount, vatAmountAttrs] = defineField('vat_amount')
const [dueDate, dueDateAttrs] = defineField('due_date')

// live-прев'ю gross_amount на клієнті — тільки для UX, сервер рахує сам
// і не довіряє цьому значенню (див. CLAUDE.md, "Архітектурні рішення" п.3)
const grossPreview = computed(() => {
  const net = Number(netAmount.value) || 0
  const vat = Number(vatAmount.value) || 0
  return Math.round((net + vat) * 100) / 100
})

const submitError = ref<string | null>(null)

const onSubmit = handleSubmit(async (formValues) => {
  submitError.value = null
  try {
    const updated = await updateInvoice(props.invoice.id, {
      net_amount: formValues.net_amount,
      vat_amount: formValues.vat_amount,
      due_date: formValues.due_date,
    })
    emit('saved', updated.data)
  } catch (e: unknown) {
    const status = e && typeof e === 'object' && 'status' in e ? (e as { status?: number }).status : undefined
    if (status === 422) {
      submitError.value = 'Дані не пройшли валідацію на сервері.'
    } else if (status === 409) {
      submitError.value = 'Статус інвойсу змінився — оновіть сторінку.'
    } else {
      submitError.value = 'Не вдалося зберегти зміни. Перевірте з’єднання і спробуйте ще раз.'
    }
  }
})
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <p v-if="!editable" class="rounded border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600">
      Редагування недоступне: інвойс має статус «{{ invoice.status }}». Редагувати можна лише інвойси зі статусом
      «pending».
    </p>

    <div>
      <label for="net_amount" class="mb-1 block text-sm font-medium">Сума нетто</label>
      <input
        id="net_amount"
        v-model="netAmount"
        v-bind="netAmountAttrs"
        type="number"
        step="0.01"
        :disabled="!editable"
        :aria-invalid="!!errors.net_amount"
        :aria-describedby="errors.net_amount ? 'net_amount-error' : undefined"
        class="w-full rounded border px-3 py-2 disabled:bg-gray-100"
      >
      <p v-if="errors.net_amount" id="net_amount-error" class="mt-1 text-sm text-red-600">{{ errors.net_amount }}</p>
    </div>

    <div>
      <label for="vat_amount" class="mb-1 block text-sm font-medium">ПДВ</label>
      <input
        id="vat_amount"
        v-model="vatAmount"
        v-bind="vatAmountAttrs"
        type="number"
        step="0.01"
        :disabled="!editable"
        :aria-invalid="!!errors.vat_amount"
        :aria-describedby="errors.vat_amount ? 'vat_amount-error' : undefined"
        class="w-full rounded border px-3 py-2 disabled:bg-gray-100"
      >
      <p v-if="errors.vat_amount" id="vat_amount-error" class="mt-1 text-sm text-red-600">{{ errors.vat_amount }}</p>
    </div>

    <div>
      <label for="gross_amount" class="mb-1 block text-sm font-medium">Сума брутто (розраховується автоматично)</label>
      <input id="gross_amount" :value="grossPreview" disabled class="w-full rounded border bg-gray-100 px-3 py-2">
    </div>

    <div>
      <label for="due_date" class="mb-1 block text-sm font-medium">Термін оплати</label>
      <input
        id="due_date"
        v-model="dueDate"
        v-bind="dueDateAttrs"
        type="date"
        :disabled="!editable"
        :aria-invalid="!!errors.due_date"
        :aria-describedby="errors.due_date ? 'due_date-error' : undefined"
        class="w-full rounded border px-3 py-2 disabled:bg-gray-100"
      >
      <p v-if="errors.due_date" id="due_date-error" class="mt-1 text-sm text-red-600">{{ errors.due_date }}</p>
    </div>

    <p v-if="submitError" role="alert" class="text-sm text-red-600">{{ submitError }}</p>

    <button
      v-if="editable"
      type="submit"
      :disabled="isSubmitting"
      class="rounded bg-indigo-600 px-4 py-2 text-white disabled:opacity-50"
    >
      Зберегти
    </button>
  </form>
</template>
