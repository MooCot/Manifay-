export type InvoiceStatus = 'pending' | 'approved' | 'rejected'

export interface Invoice {
  id: number
  number: string
  supplier_name: string
  supplier_tax_id: string
  // decimal-колонки серіалізуються Laravel'ом як string (decimal:2 cast) —
  // навмисно, щоб уникнути втрати точності при JSON-парсингу float на клієнті
  net_amount: string
  vat_amount: string
  gross_amount: string
  currency: string
  status: InvoiceStatus
  issue_date: string
  due_date: string
  created_at: string
  updated_at: string
}

export interface InvoiceListResponse {
  data: Invoice[]
}

export interface InvoiceResponse {
  data: Invoice
}
