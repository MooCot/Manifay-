import type { InvoiceListResponse } from '~/types/invoice'

export function useInvoices(page: Ref<number>, sort: Ref<string>, direction: Ref<'asc' | 'desc'>) {
  const config = useRuntimeConfig()

  return useFetch<InvoiceListResponse>('/invoices', {
    baseURL: config.public.apiBase,
    query: { page, sort, direction },
    watch: [page, sort, direction],
    // lazy — без цього top-level await на сторінці блокує рендер компонента
    // (і skeleton всередині нього) до резолву фетчу; skeleton фізично
    // нема коли показати при першому заході на сторінку
    lazy: true,
  })
}
