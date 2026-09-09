import type { Invoice, InvoiceResponse } from '~/types/invoice'

export function useInvoice(id: string | number) {
  const config = useRuntimeConfig()

  return useFetch<InvoiceResponse>(`/invoices/${id}`, {
    baseURL: config.public.apiBase,
    lazy: true,
  })
}

export interface UpdateInvoicePayload {
  net_amount: number
  vat_amount: number
  due_date: string
}

export function updateInvoice(id: string | number, payload: UpdateInvoicePayload) {
  const config = useRuntimeConfig()

  return $fetch<InvoiceResponse>(`/invoices/${id}`, {
    baseURL: config.public.apiBase,
    method: 'PUT',
    body: payload,
  })
}

export type { Invoice }
