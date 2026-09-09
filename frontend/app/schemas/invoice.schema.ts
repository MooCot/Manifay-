import { z } from 'zod'

/**
 * issueDate передається ззовні (не є полем форми — issue_date не редагується,
 * див. CLAUDE.md "Архітектурні рішення" п.4), тому крос-польова перевірка
 * due_date >= issue_date звіряється проти фіксованого значення, а не проти
 * інших полів самої схеми.
 */
export function createInvoiceEditSchema(issueDate: string) {
  return z
    .object({
      net_amount: z.coerce.number().positive('Сума нетто має бути більшою за 0'),
      vat_amount: z.coerce.number().min(0, 'ПДВ не може бути відʼємним'),
      due_date: z.coerce.date(),
    })
    .refine((values) => values.due_date >= new Date(issueDate), {
      message: 'Дата оплати не може бути раніше дати видачі',
      path: ['due_date'],
    })
}
