import type { InvoiceListResponse } from '~/types/invoice'

export function useInvoices(page: Ref<number>, sort: Ref<string>, direction: Ref<'asc' | 'desc'>) {
  const config = useRuntimeConfig()

  return useFetch<InvoiceListResponse>('/invoices', {
    baseURL: config.public.apiBase,
    query: { page, sort, direction },
    watch: [page, sort, direction],
    lazy: true,
  })
}
