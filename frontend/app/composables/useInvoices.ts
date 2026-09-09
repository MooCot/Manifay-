import type { InvoiceListResponse } from '~/types/invoice'

export function useInvoices() {
  const config = useRuntimeConfig()

  return useFetch<InvoiceListResponse>('/invoices', {
    baseURL: config.public.apiBase,
  })
}
