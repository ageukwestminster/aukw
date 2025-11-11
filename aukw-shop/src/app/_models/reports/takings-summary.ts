export class TakingsSummary {
  id: number;
  date: Date;
  shopid: number;
  number_of_items_sold: number;
  customers_num_total: number;
  sales_total: number;
  rag: number;
  sales_total_inc_rag: number;
  expenses: number;
  cash_difference: number;
  total_after_expenses: number;
  daily_net_sales: number;
  comments: string;
  quickbooks: Boolean;
  cash_to_bank: number;
  credit_cards: number;

  isDeleting: boolean = false;
  isUpdating: boolean = false;

  /** 
   * Add the values from a specified payslip to this instance
   * @param IrisPayslip The payslip to add to this instance.
   * @returns This instance
   */
  add(obj: TakingsSummary): TakingsSummary {
    this.number_of_items_sold += (obj && obj.number_of_items_sold) || 0;
    this.customers_num_total += (obj && obj.customers_num_total) || 0;
    this.sales_total += (obj && obj.sales_total) || 0;
    this.rag += (obj && obj.rag) || 0;
    this.sales_total_inc_rag += (obj && obj.sales_total_inc_rag) || 0;
    this.expenses += (obj && obj.expenses) || 0;
    this.total_after_expenses += (obj && obj.total_after_expenses) || 0;
    this.cash_difference += (obj && obj.cash_difference) || 0;
    this.daily_net_sales += (obj && obj.daily_net_sales) || 0;
    this.cash_to_bank += (obj && obj.cash_to_bank) || 0;
    this.credit_cards += (obj && obj.credit_cards) || 0;
    return this;
  }

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.date = (obj && obj.date) || null;
    this.shopid = (obj && obj.shopid) || null;
    this.number_of_items_sold = (obj && obj.number_of_items_sold) || null;
    this.customers_num_total = (obj && obj.customers_num_total) || null;
    this.sales_total = (obj && obj.sales_total) || null;
    this.rag = (obj && obj.rag) || null;
    this.sales_total_inc_rag = (obj && obj.sales_total_inc_rag) || null;
    this.expenses = (obj && obj.expenses) || null;
    this.total_after_expenses = (obj && obj.total_after_expenses) || null;
    this.cash_difference = (obj && obj.cash_difference) || null;
    this.comments = (obj && obj.comments) || null;
    this.daily_net_sales = (obj && obj.daily_net_sales) || null;
    this.quickbooks = (obj && obj.quickbooks) || null;
    this.cash_to_bank = (obj && obj.cash_to_bank) || null;
    this.credit_cards = (obj && obj.credit_cards) || null;
  }
}
