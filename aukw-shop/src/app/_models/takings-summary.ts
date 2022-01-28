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

    isDeleting: boolean = false;
    isUpdating: boolean = false;

    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.date = obj && obj.date || null;
        this.shopid = obj && obj.shopid || null;
        this.number_of_items_sold = obj && obj.number_of_items_sold || null;
        this.customers_num_total = obj && obj.customers_num_total || null;
        this.sales_total = obj && obj.sales_total || null;
        this.rag = obj && obj.rag || null;
        this.sales_total_inc_rag = obj && obj.sales_total_inc_rag || null;
        this.expenses = obj && obj.expenses || null;
        this.total_after_expenses = obj && obj.total_after_expenses || null;
        this.cash_difference = obj && obj.cash_difference || null;
        this.comments = obj && obj.comments || null;
        this.daily_net_sales = obj && obj.daily_net_sales || null;
        this.quickbooks = obj && obj.quickbooks || null;
    }
}