import { z } from 'zod'

export function createInvoiceEditSchema(issueDate: string) {
  return z
    .object({
      net_amount: z.coerce.number().positive('Сума нетто має бути більшою за 0'),
      vat_amount: z.coerce.number().min(0, 'ПДВ не може бути відʼємним'),
      due_date: z.string().min(1, 'Дата оплати обовʼязкова'),
    })
    .refine((values) => new Date(values.due_date) >= new Date(issueDate), {
      message: 'Дата оплати не може бути раніше дати видачі',
      path: ['due_date'],
    })
}
