import type { InvoiceListResponse } from '~/types/invoice'

export function useInvoices(page: Ref<number>) {
  const config = useRuntimeConfig()

  return useFetch<InvoiceListResponse>('/invoices', {
    baseURL: config.public.apiBase,
    query: { page },
    watch: [page],
  })
}
